import requests
import json
import tempfile
import asyncio
import aiohttp
import os
import numpy as np
import sys
import hashlib
import time
import math
import threading
import shutil
import aiofiles

from io import BytesIO
from zfec import Encoder, Decoder

from dynostore.controllers.catalogs import CatalogController
from dynostore.datamanagement.reliability import encoder
from drex.utils.load_data import RealRecords
from drex.utils.prediction import Predictor

class DataController:
    predictor = Predictor()
    real_records = RealRecords(dir_data="data/")
    catalog_cache = {}
    CHUNK_SIZE = 64 * 1024  # 64KB

    @staticmethod
    def evict_cache(max_files=100):
        cache_dir = ".cache"
        entries = []
        for root, _, files in os.walk(cache_dir):
            for name in files:
                path = os.path.join(root, name)
                try:
                    mtime = os.path.getmtime(path)
                    entries.append((mtime, path))
                except FileNotFoundError:
                    continue
        if len(entries) > max_files:
            entries.sort()
            for _, path in entries[:-max_files]:
                try:
                    os.remove(path)
                except Exception:
                    continue

    @staticmethod
    def _http_url(route):
        return route if route.startswith("http") else f"http://{route}"

    @staticmethod
    def _get_cache_path(token_user, key_object):
        safe_user = hashlib.sha1(token_user.encode()).hexdigest()
        cache_dir = os.path.join(".cache", safe_user)
        os.makedirs(cache_dir, exist_ok=True)
        return os.path.join(cache_dir, f"{key_object}.bin")

    @staticmethod
    async def delete_object(token_user, key_object, metadata_service):
        url = f"http://{metadata_service}/api/storage/{token_user}/{key_object}"
        async with aiohttp.ClientSession() as session:
            async with session.delete(url) as resp:
                return await resp.json(), resp.status

    @staticmethod
    async def exists_object(token_user, key_object, metadata_service):
        url = f"http://{metadata_service}/api/storage/{token_user}/{key_object}/exists"
        async with aiohttp.ClientSession() as session:
            async with session.get(url) as resp:
                return await resp.json(), resp.status

    @staticmethod
    async def download_chunk(session, route, key_object):
        chunk_id = 0
        if "chunk" in route:
            chunk_id = int(route["chunk"]["name"].split("_")[0].replace("c", "")) - 1
        url = DataController._http_url(route['route'])
        async with session.get(url) as resp:
            resp.raise_for_status()
            data = await resp.read()
        return chunk_id, data

    @staticmethod
    async def pull_data(token_user, key_object, metadata_service, force_refresh=False):
        decode_start = time.perf_counter_ns()

        cache_path = DataController._get_cache_path(token_user, key_object)
        if not force_refresh and os.path.exists(cache_path):
            print(f"Serving {key_object} from local cache", flush=True)
            with open(cache_path, "rb") as f:
                obj = f.read()
            return obj, 200, {'Content-Type': 'application/octet-stream', "is_encrypted": False, "time_decode": 0}

        # Check for pending marker
        object_path = os.path.join(".temp", key_object)
        marker_path = f"{object_path}.pending"
        if os.path.exists(marker_path):
            print("PENDING")
            object_path = marker_path.replace(".pending", "")
            if os.path.exists(object_path):
                with open(object_path, "rb") as f:
                    obj = f.read()
                return obj, 200, {'Content-Type': 'application/octet-stream', "is_encrypted": False, "time_decode": 0}    
        
        url = f"http://{metadata_service}/api/storage/{token_user}/{key_object}"
        async with aiohttp.ClientSession() as session:
            async with session.get(url) as resp:
                if resp.status >= 400:
                    return await resp.json(), resp.status
                result = await resp.json()

        routes = result['data']['routes']
        metadata_object = result['data']['file']

        async with aiohttp.ClientSession() as session:
            tasks = [DataController.download_chunk(session, route, key_object) for route in routes]
            results = await asyncio.gather(*tasks)

        if metadata_object["required_chunks"] == 1:
            obj = results[0][1]
        else:
            sorted_results = sorted(results, key=lambda x: x[0])
            chunk_data = [data for _, data in sorted_results[:metadata_object['required_chunks']]]
            chunk_indices = [idx for idx, _ in sorted_results[:metadata_object['required_chunks']]]
            k = metadata_object['required_chunks']
            n = metadata_object['chunks']
            original_size = metadata_object.get('original_size')
            decoder = Decoder(k, n)
            recovered_blocks = decoder.decode(chunk_data, chunk_indices)
            obj = b''.join(recovered_blocks)
            if original_size is not None:
                obj = obj[:original_size]

        with open(cache_path, "wb") as f:
            f.write(obj)
        os.utime(cache_path, None)

        decode_end = time.perf_counter_ns()
        return obj, 200, {'Content-Type': 'application/octet-stream', "is_encrypted": metadata_object['is_encrypted'], "time_decode": decode_end - decode_start}

    @staticmethod
    def _resilient_distribution(data_bytes, token_user, metadata_service):
        chunk_start = time.perf_counter_ns()

        servers = requests.get(f"http://{metadata_service}/api/servers/{token_user}").json()
        k, n = 2, 5
        encoder_obj = Encoder(k, n)

        if len(data_bytes) == 0:
            raise ValueError("Input file is empty")

        block_size = int(math.ceil(len(data_bytes) / float(k)))
        buf = np.frombuffer(data_bytes, dtype=np.uint8)
        pad_len = block_size * k - len(buf)
        buf = np.pad(buf, (0, pad_len), constant_values=0)
        blocks = [buf[i * block_size:(i + 1) * block_size].tobytes() for i in range(k)]

        fragments = encoder_obj.encode(blocks)
        chunk_end = time.perf_counter_ns()

        return fragments, n, k, chunk_end - chunk_start

    @staticmethod
    async def _upload_chunk(session, url, data):
        try:
            async with session.put(url, data=data) as resp:
                if resp.status != 201:
                    raise Exception(f"Upload failed to {url}: {resp.status}")
        except Exception as e:
            raise e

    @staticmethod
    def _get_cached_catalog(token_user, catalog):
        return DataController.catalog_cache.get((token_user, catalog))

    @staticmethod
    def _set_cached_catalog(token_user, catalog, catalog_result):
        DataController.catalog_cache[(token_user, catalog)] = catalog_result

    @staticmethod
    def _background_erasure_coding(object_path, key_object, token_user, metadata_service,nodes):
        try:
            with open(object_path, "rb") as f:
                data_bytes = f.read()

            fragments, n, k, chunk_time = DataController._resilient_distribution(data_bytes, token_user, metadata_service)

            for i, fragment in enumerate(fragments):
                url = DataController._http_url(nodes[i]['route'])
                with requests.Session() as session:
                    adapter = requests.adapters.HTTPAdapter(
                        max_retries=requests.adapters.Retry(total=3, backoff_factor=0.3)
                    )
                    session.mount('http://', adapter)
                    session.mount('https://', adapter)
                    res = session.put(url, data=fragment)
                    print("RESULT", res)

            # Remove pending marker
            marker_path = f"{object_path}.pending"
            if os.path.exists(marker_path):
                os.remove(marker_path)

            print(f"[PASSIVE EC] Done erasure coding {key_object} in {chunk_time / 1e6:.2f} ms")
        except Exception as e:
            print(f"[PASSIVE EC] Error during erasure coding: {e}")

    @staticmethod
    def push_data(request, metadata_service, pubsub_service, catalog, token_user, key_object):
        # start_total = time.perf_counter_ns()

        # time_marks = {}

        # # Read and parse files
        # start_parse = time.perf_counter_ns()
        # files = request.files
        # end_parse = time.perf_counter_ns()
        # time_marks['parse_time_0'] = end_parse - start_parse
        # request_json = json.loads(files['json'].read().decode('utf-8'))
        # #request_bytes = files['data'].read()
        # end_parse = time.perf_counter_ns()
        # time_marks['parse_time_1'] = end_parse - start_parse
        # start_parse = time.perf_counter_ns()
        # file_path = os.path.join(".temp", request_json['key'])
        # with open(file_path, 'wb') as f:
        #     shutil.copyfileobj(files['data'].stream, f)
        # end_parse = time.perf_counter_ns()
        # time_marks['parse_time_2'] = end_parse - start_parse

        # passive_ec = True
        # #print(f"[DEBUG] Uploaded object size: {len(request_bytes)} bytes")

        # #if len(request_bytes) == 0:
        # #    return {"error": "Uploaded data is empty."}, 400

        # # Write to temp file
        # #start_write = time.perf_counter_ns()
        # #object_path = os.path.join(".temp", request_json['key'])
        # #with open(object_path, 'wb') as f:
        # #    f.write(request_bytes)
        # #end_write = time.perf_counter_ns()
        # #time_marks['write_time'] = end_write - start_write

        # # Catalog creation or retrieval
        # start_catalog = time.perf_counter_ns()
        # catalog_result, status = CatalogController.createOrGetCatalog(request, pubsub_service, catalog, token_user)
        # end_catalog = time.perf_counter_ns()
        # time_marks['catalog_time'] = end_catalog - start_catalog

        # if status not in [201, 302]:
        #     return catalog_result, status

        # token_catalog = catalog_result['data']['tokencatalog']
        # metadata_url = f"http://{metadata_service}/api/storage/{token_user}/{token_catalog}/{key_object}"

        # # Register metadata
        # request_json['chunks'] = 5
        # request_json['required_chunks'] = 2
        # request_json['coding_status'] = 'pending'

        # start_metadata = time.perf_counter_ns()
        # resp = requests.put(metadata_url, json=request_json)
        # end_metadata = time.perf_counter_ns()
        # time_marks['metadata_register_time'] = end_metadata - start_metadata

        # if resp.status_code != 201:
        #     return resp.json(), resp.status_code

        # nodes = resp.json()['nodes']
        # url = DataController._http_url(nodes[0]['route'])

        # # Write pending marker
        # start_marker = time.perf_counter_ns()
        # marker_path = f"{file_path}.pending"
        # with open(marker_path, "w") as marker:
        #     marker.write("pending")
        # end_marker = time.perf_counter_ns()
        # time_marks['marker_time'] = end_marker - start_marker

        # # Initial upload of original object to first node
        # start_upload = time.perf_counter_ns()
        # # with requests.Session() as session:
        # #     adapter = requests.adapters.HTTPAdapter(
        # #         max_retries=requests.adapters.Retry(total=3, backoff_factor=0.3)
        # #     )
        # #     session.mount('http://', adapter)
        # #     session.mount('https://', adapter)
        # #     upload_resp = session.put(url, data=request_bytes)
        # end_upload = time.perf_counter_ns()
        # time_marks['time_upload'] = end_upload - start_upload

        # #if upload_resp.status_code != 201:
        # #    return upload_resp.json(), upload_resp.status_code

        # # Start background EC
        # start_thread = time.perf_counter_ns()
        # thread = threading.Thread(
        #     target=DataController._background_erasure_coding,
        #     args=(file_path, key_object, token_user, metadata_service, nodes),
        #     daemon=passive_ec
        # )
        # thread.start()
        # end_thread = time.perf_counter_ns()
        # time_marks['ec_thread_start_time'] = end_thread - start_thread
        # print("AAA", passive_ec)
        # if not passive_ec:
        #     print("ENTROOO")
        #     thread.join()

        # # Register object in catalog
        # start_register = time.perf_counter_ns()
        # reg_result, reg_status = CatalogController.registFileInCatalog(
        #     pubsub_service, token_catalog, token_user, key_object)
        # end_register = time.perf_counter_ns()
        # time_marks['catalog_register_time'] = end_register - start_register

        # if reg_status != 201:
        #     return reg_result, reg_status

        # time_marks['total_time'] = time.perf_counter_ns() - start_total

        # return {
        #     "key_object": key_object,
        #     "passive_ec": True,
        #     **{k: round(v / 1e6, 3) for k, v in time_marks.items()}  # ms
        # }, 201

        pass

    @staticmethod
    async def upload_metadata(request, token_user, key_object):
        if not request:
            return {"error": "Invalid JSON"}, 400

        metadata_path = f".temp/{key_object}.json"
        data = await request.get_data()
        data = data.decode('utf-8')
        os.makedirs(os.path.dirname(metadata_path), exist_ok=True)
        with open(metadata_path, "w") as f:
            json.dump(data, f)

        return {"status": "metadata received"}, 200

    @staticmethod
    async def upload_data(request, metadata_service, pubsub_service, catalog, token_user, key_object, read_time_ns=None):
        start_time = time.perf_counter_ns()
        time_marks = {}

        metadata_path = f".temp/{key_object}.json"
        if not os.path.exists(metadata_path):
            return {"error": "Missing metadata for object"}, 400

        with open(metadata_path) as f:
            request_json = json.load(f)

        # if it's a string (e.g., badly serialized JSON like '"{...}"'), parse again
        if isinstance(request_json, str):
            request_json = json.loads(request_json)

        print(type(request_json), flush=True)
    
        print(request_json, flush=True)

        object_path = f".temp/{key_object}"
        os.makedirs(os.path.dirname(object_path), exist_ok=True)

        start_read = time.perf_counter_ns()
        #async with timeout(app.config['BODY_TIMEOUT']):
        #async with aiofiles.open(object_path, "wb") as f:
        with open(object_path, "wb") as f:
            async for data in request.body:
                f.write(data)
            
            
        # async with await request.body as body_stream:
        #     with open(object_path, "wb") as f:
        #         while True:
        #             chunk = await body_stream.read(DataController.CHUNK_SIZE)
        #             if not chunk:
        #                 break
        #             f.write(chunk)
        end_read = time.perf_counter_ns()
        time_marks['stream_read_time'] = end_read - start_read

        marker_path = f"{object_path}.pending"
        with open(marker_path, "w") as marker:
            marker.write("pending")

        start_catalog = time.perf_counter_ns()
        catalog_result, status = CatalogController.createOrGetCatalog(request, pubsub_service, catalog, token_user)
        end_catalog = time.perf_counter_ns()
        time_marks['catalog_time'] = end_catalog - start_catalog

        if status not in [201, 302]:
            return catalog_result, status

        token_catalog = catalog_result['data']['tokencatalog']

        request_json['chunks'] = 5
        request_json['required_chunks'] = 2
        request_json['coding_status'] = 'pending'

        metadata_url = f"http://{metadata_service}/api/storage/{token_user}/{token_catalog}/{key_object}"
        resp = requests.put(metadata_url, json=request_json)

        if resp.status_code != 201:
            return resp.json(), resp.status_code

        nodes = resp.json()['nodes']

        thread = threading.Thread(
            target=DataController._background_erasure_coding,
            args=(object_path, key_object, token_user, metadata_service, nodes),
            daemon=True
        )
        thread.start()

        end_time = time.perf_counter_ns()
        time_marks['total_tie'] = end_time - start_time

        return {
            "key_object": key_object,
            "passive_ec": True,
            **{k: round(v / 1e6, 3) for k, v in time_marks.items()}  # ms
        }, 201

