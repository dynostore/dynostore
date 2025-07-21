import requests
import json
import pickle
import multiprocessing
import time
import numpy as np
import sys
import os
import subprocess


from dynostore.controllers.catalogs import CatalogController
from dynostore.datamanagement.reliability import ida
from drex.utils.load_data import RealRecords
from drex.utils.prediction import Predictor
from drex.schedulers.algorithm4 import algorithm4, system_saturation


class DataController:
    predictor = Predictor()
    real_records = RealRecords(dir_data="data/")

    @staticmethod
    def _http_url(route):
        return route if route.startswith("http") else f"http://{route}"

    @staticmethod
    def delete_object(request, token_user, key_object, metadata_service):
        url = f"http://{metadata_service}/api/storage/{token_user}/{key_object}"
        resp = requests.delete(url)
        return resp.json(), resp.status_code

    @staticmethod
    def exists_object(request, token_user, key_object, metadata_service):
        url = f"http://{metadata_service}/api/storage/{token_user}/{key_object}/exists"
        resp = requests.get(url)
        return resp.json(), resp.status_code

    @staticmethod
    def download_chunk(route):
        url = DataController._http_url(route['route'])
        resp = requests.get(url)
        resp.raise_for_status()
        return resp.content

    @staticmethod
    def pull_data(request, token_user, key_object, metadata_service, pubsub_service):
        url = f"http://{metadata_service}/api/storage/{token_user}/{key_object}"
        resp = requests.get(url)
        if resp.status_code >= 400:
            return resp.json(), resp.status_code

        routes = resp.json()['data']['routes']
        with multiprocessing.Pool(multiprocessing.cpu_count()) as pool:
            results = pool.map(DataController.download_chunk, routes)

        if len(results) > 1:
            chunks = [pickle.loads(r) for r in results]
            obj = ida.assemble_bytes(chunks)
        else:
            obj = results[0]

        return obj, 200, {'Content-Type': 'application/octet-stream'}

    @staticmethod
    def _resilient_distribution(path_object, size, token_user, metadata_service):
        server_url = f"http://{metadata_service}/api/servers/{token_user}"
        servers = requests.get(server_url).json()
        print(f"Servers: {servers}", flush=True)
        num_nodes = len(servers)
        node_sizes = [int(s['storage']) for s in servers]

        reliability_nodes = np.random.rand(num_nodes) * (0.3 - 0.01) + 0.01
        bandwidths = [10] * num_nodes
        file_size_mb = size / 1024 / 1024

        n, k = None, None
        nodes, n, k, _ = algorithm4(
            num_nodes,
            reliability_nodes,
            bandwidths,
            0.99,
            file_size_mb,
            DataController.real_records,
            node_sizes,
            max(node_sizes) / 1024 / 1024,
            sys.maxsize,
            system_saturation,
            sum(node_sizes) / 1024 / 1024,
            DataController.predictor
        )

        current_path = os.getcwd()
        print(current_path, flush=True)
        
        # Call the C code to split the bytes
        proc = subprocess.Popen(
            ["/app/dynostore/datamanagement/reliability/IDA/Dis", str(n), str(k), str(16), str(path_object)],
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE
        )

        stdout_data, stderr_data = proc.communicate()
        print("STDOUT:", stdout_data.decode('utf-8'), flush=True)
        print("STDERR:", stderr_data.decode('utf-8'), flush=True)
        exit_code = proc.returncode

        if exit_code != 0:
            print(f"Error in subprocess: {stderr_data.decode('utf-8')}", flush=True)
            print(f"Exit code: {exit_code}", flush=True)
            
            # Raise an exception or handle the error as needed
            raise RuntimeError(f"IDA Dis command failed with exit code {exit_code}")

        #subprocess.run(["IDA", "Dis", str(n), str(k), str(16), str(path_object)], check=True)

        #chunks = ida.split_bytes(request_bytes, n, k)
        #return [pickle.dumps(c) for c in chunks], n, k
        return n,k

    @staticmethod
    def push_data(request, metadata_service, pubsub_service, catalog, token_user, key_object):
        start_time = time.perf_counter_ns()
        files = request.files
        request_json = json.loads(files['json'].read().decode('utf-8'))
        request_bytes = files['data'].read()

        # Write the object to FS in a temporary location
        temp_dir = os.path.join(os.getcwd(), '.temp')
        os.makedirs(temp_dir, exist_ok=True)
        
        object_path = os.path.join(temp_dir, request_json['key'])
        with open(object_path, 'wb') as f:
            f.write(request_bytes)
            
        if request_json.get('resiliency') == 1:
            n, k = DataController._resilient_distribution(object_path, len(request_bytes), token_user, metadata_service)
        else:
            data = [request_bytes]
            n = k = 1

        request_json['chunks'] = n
        request_json['required_chunks'] = k

        catalog_result, status = CatalogController.createOrGetCatalog(
            request, pubsub_service, catalog, token_user)

        if status not in [201, 302]:
            return catalog_result, status

        token_catalog = catalog_result['data']['tokencatalog']
        metadata_url = f"http://{metadata_service}/api/storage/{token_user}/{token_catalog}/{key_object}"
        resp = requests.put(metadata_url, json=request_json)

        if resp.status_code != 201:
            return resp.json(), resp.status_code

        nodes = resp.json()['nodes']
        upload_start = time.perf_counter_ns()

        for i, node in enumerate(nodes):
            url = DataController._http_url(node['route'])
            upload_resp = requests.put(url, data=data[i])
            if upload_resp.status_code != 201:
                return upload_resp.json(), upload_resp.status_code

        upload_end = time.perf_counter_ns()

        reg_result, reg_status = CatalogController.registFileInCatalog(
            pubsub_service, token_catalog, token_user, key_object)
        
        if reg_status != 201:
            return reg_result, reg_status

        end_time = time.perf_counter_ns()
        return {
            "total_time": end_time - start_time,
            "time_upload": upload_end - upload_start,
            "key_object": key_object
        }, 201
