import requests
import json
import pickle

from dynostore.controllers.catalogs import CatalogController
from drex.utils.reliability import ida
from drex.utils.load_data import RealRecords

from drex.schedulers.algorithm4 import *
import numpy as np
import sys



class DataController():

    def deleteObject(
        request,
        tokenUser: str,
        keyObject: str,
        metadaService: str
    ):
        url_to_delete = f'http://{metadaService}/api/storage/{tokenUser}/{keyObject}'
        response = requests.delete(url_to_delete)
        return response.json(), response.status_code

    def existsObject(
        request,
        tokenUser: str,
        keyObject: str,
        metadaService: str
    ):
        url_to_pull_metadata = f'http://{metadaService}/api/storage/{tokenUser}/{keyObject}/exists'
        response = requests.get(url_to_pull_metadata)
        return response.json(), response.status_code

    def pullData(
        request,
        tokenUser: str,
        keyObject: str,
        metadaService: str,
        pubsubService: str
    ):
        url_to_pull_metadata = f'http://{metadaService}/api/storage/{tokenUser}/{keyObject}'
        response = requests.get(url_to_pull_metadata)
        
        print(response.text, flush=True)

        if response.status_code >= 400:
            return response.json(), response.status_code

        print(response.text, flush=True)
        data = response.json()
        
        routes = data['data']['routes']
        results = []
        objectRes = None
        
        for route in routes:
            url_node = route['route']
            url_node = url_node if url_node.startswith("http") else f'http://{url_node}'
            response = requests.get(url_node)
            if response.status_code != 200:
                return response.json(), response.status_code
            results.append(response.content)
            
        
        if len(results) > 1:
            results = [pickle.loads(fragment) for fragment in results]
            objectRes = ida.assemble_bytes(results)
        else:
            objectRes = results[0]

        return objectRes, 200, {'Content-Type': 'application/octet-stream'}

    def pushData(
        request,
        metadaService: str,
        pubsubService: str,
        catalog: str,
        tokenUser: str,
        keyObject: str,
        predictor
    ):
        files = request.files
        
        request_json = json.loads(files['json'].read().decode('utf-8'))
        request_bytes = files['data'].read()
        print(request_json, flush=True)
        data = []
        if request_json['resiliency'] > 1:
            #n = request_json['chunks']
            # = request_json['required_chunks']
            
            # get available nodes
            url_service = f"http://{metadaService}/api/servers/{tokenUser}"
            results = requests.get(url_service)
            
            print(results, flush=True)
            servers = results.json()['data']
            numberNodes = len(servers)
            
            reliability_nodes = np.random.rand(numberNodes)
            max_rel = 0.3
            min_rel = 0.01
            reliability_nodes = reliability_nodes * (max_rel - min_rel) + min_rel
            bandwidths = [10] * numberNodes
            # Reliability min we want to meet
            reliability_threshold = 0.99
            file_size_in_mb = len(request_bytes) / 1024 / 1024
            real_records = RealRecords(dir_data="data/")
            max_node_size = max([server['size'] for server in servers])
            min_data_size = sys.maxsize
            total_node_size = sum([server['size'] for server in servers])
            
            nodes, n, k, node_sizes = algorithm4(numberNodes, reliability_nodes,
                                         bandwidths, reliability_threshold, file_size_in_mb, real_records, node_sizes,
                                         max_node_size, min_data_size, system_saturation, total_node_size, predictor)
            data = ida.split_bytes(request_bytes, n, k)
            data = [pickle.dumps(fragment) for fragment in data]
        else:
            data.append(request_bytes)
            

        results, status_code = CatalogController.createOrGetCatalog(
            request, pubsubService, catalog, tokenUser)

        if status_code == 201 or status_code == 302:

            tokenCatalog = results['data']['tokencatalog']

            url_to_push_metadata = f'http://{metadaService}/api/storage/{tokenUser}/{tokenCatalog}/{keyObject}'

            response = requests.put(url_to_push_metadata, json=request_json)
            print("holas",response.text, flush=True)
            if response.status_code != 201:
                return response.json(), response.status_code

            nodes = response.json()['nodes']

            for i,node in enumerate(nodes):
                print(node, flush=True)
                url_node = node['route']
                url_node = url_node if url_node.startswith("http") else f'http://{url_node}'
                response = requests.put(url_node, data=data[i])
                print(response.text, flush=True)
                if response.status_code != 201:
                    return response.json(), response.status_code

            results, code = CatalogController.registFileInCatalog(
                pubsubService, tokenCatalog, tokenUser, keyObject)

            if code != 201:
                return results, code

            return "Objects pushed successfully", response.status_code
        else:
            return results, status_code
