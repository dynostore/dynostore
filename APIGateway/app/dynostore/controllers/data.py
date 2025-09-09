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
import logging

from io import BytesIO
from zfec import Encoder, Decoder

from dynostore.controllers.catalogs import CatalogController
from dynostore.datamanagement.reliability import encoder
from drex.utils.load_data import RealRecords
from drex.utils.prediction import Predictor


logger = logging.getLogger(__name__)


def _log(level: str, operation: str, key: str, phase: str, status: str, msg: str = ""):
    """
    Emit a log in the format:
    SERVICE, OPERATION, OBJECTKEY, START/END, Status, MSG
    """
    rec = f"DATACONTROLLER,{operation},{key},{phase},{status},{msg}"
    level = level.lower()
    if level == "debug":
        logger.debug(rec)
    elif level == "info":
        logger.info(rec)
    elif level == "warning":
        logger.warning(rec)
    elif level == "error":
        logger.error(rec)
    else:
        logger.debug(rec)


class DataController:
    predictor = Predictor()
    real_records = RealRecords(dir_data="data/")
    catalog_cache = {}
    CHUNK_SIZE = 64 * 1024  # 64KB

    @staticmethod
    def evict_cache(max_files=100):
        _log("debug", "EVICT_CACHE", "-", "START", "INIT", f"max_files={max_files}")
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
        removed = 0
        if len(entries) > max_files:
            entries.sort()
            for _, path in entries[:-max_files]:
                try:
                    os.remove(path)
                    removed += 1
                except Exception as e:
                    _log("warning", "EVICT_CACHE", "-", "END", "REMOVE_ERROR", f"path={path};msg={e}")
        _log("debug", "EVICT_CACHE", "-", "END", "SUCCESS", f"kept={len(entries)-removed};removed={removed}")

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
        _log("debug", "DELETE", key_object, "START", "INIT", f"url={url}")
        async with aiohttp.ClientSession() as session:
            try:
                async with session.delete(url) as resp:
                    payload = await resp.json()
                    _log("debug", "DELETE", key_object, "END", "SUCCESS", f"status={resp.status}")
                    return payload, resp.status
            except Exception as e:
                _log("error", "DELETE", key_object, "END", "ERROR", f"url={url};msg={e}")
                return {"error": str(e)}, 500

    @staticmethod
    async def exists_object(token_user, key_object, metadata_service):
        url = f"http://{metadata_service}/api/storage/{token_user}/{key_object}/exists"
        _log("debug", "EXISTS", key_object, "START", "INIT", f"url={url}")
        async with aiohttp.ClientSession() as session:
            try:
                async with session.get(url) as resp:
                    payload = await resp.json()
                    _log("debug", "EXISTS", key_object, "END", "SUCCESS",
                         f"status={resp.status};exists={payload.get('exists')}")
                    return payload, resp.status
            except Exception as e:
                _log("error", "EXISTS", key_object, "END", "ERROR", f"url={url};msg={e}")
                return {"error": str(e)}, 500

    @staticmethod
    async def download_chunk(session, route, key_object):
        chunk_id = 0
        if "chunk" in route:
            try:
                chunk_id = int(route["chunk"]["name"].split("_")[0].replace("c", "")) - 1
            except Exception:
                chunk_id = 0
        url = DataController._http_url(route['route'])
        t0 = time.perf_counter_ns()
        _log("debug", "DOWNLOAD_CHUNK", key_object, "START", "RUN", f"chunk_id={chunk_id};url={url}")
        async with session.get(url) as resp:
            resp.raise_for_status()
            data = await resp.read()
        dt_ms = (time.perf_counter_ns() - t0) / 1e6
        _log("debug", "DOWNLOAD_CHUNK", key_object, "END", "SUCCESS",
             f"chunk_id={chunk_id};bytes={len(data)};time_ms={dt_ms:.3f}")
        return chunk_id, data

    @staticmethod
    async def pull_data(token_user, key_object, metadata_service, force_refresh=False):
        _log("debug", "PULL", key_object, "START", "INIT",
             f"user={token_user};force_refresh={force_refresh}")
        decode_start = time.perf_counter_ns()
        timeline_path = f".temp/{key_object}.timeline.json"
        timeline = {}
        timeline["pull_start"] = time.time_ns()

        cache_path = DataController._get_cache_path(token_user, key_object)
        if not force_refresh and os.path.exists(cache_path):
            _log("debug", "PULL", key_object, "END", "CACHE_HIT", f"path={cache_path}")
            with open(cache_path, "rb") as f:
                obj = f.read()
            timeline["pull_end"] = time.time_ns()
            os.makedirs(os.path.dirname(timeline_path), exist_ok=True)
            with open(timeline_path, "w") as f:
                json.dump(timeline, f, indent=2)
            return obj, 200, {'Content-Type': 'application/octet-stream', "is_encrypted": False, "time_decode": 0}

        object_path = os.path.join(".temp", key_object)
        marker_path = f"{object_path}.pending"
        if os.path.exists(marker_path):
            _log("debug", "PULL", key_object, "START", "PENDING_MARKER", f"marker={marker_path}")
            object_path = marker_path.replace(".pending", "")
            if os.path.exists(object_path):
                with open(object_path, "rb") as f:
                    obj = f.read()
                timeline["pull_end"] = time.time_ns()
                os.makedirs(os.path.dirname(timeline_path), exist_ok=True)
                with open(timeline_path, "w") as f:
                    json.dump(timeline, f, indent=2)
                _log("debug", "PULL", key_object, "END", "PENDING_SERVED", f"path={object_path}")
                return obj, 200, {'Content-Type': 'application/octet-stream', "is_encrypted": False, "time_decode": 0}

        metadata_retrieval_start = time.perf_counter_ns()
        url = f"http://{metadata_service}/api/storage/{token_user}/{key_object}"
        try:
            async with aiohttp.ClientSession() as session:
                async with session.get(url) as resp:
                    if resp.status >= 400:
                        _log("error", "PULL_METADATA", key_object, "END", "ERROR",
                             f"status={resp.status};url={url}")
                        return await resp.json(), resp.status
                    result = await resp.json()
        except Exception as e:
            _log("error", "PULL_METADATA", key_object, "END", "EXCEPTION", f"url={url};msg={e}")
            return {"error": str(e)}, 500

        routes = result['data']['routes']
        metadata_object = result['data']['file']
        metadata_retrieval_end = time.perf_counter_ns()
        _log("debug", "PULL_METADATA", key_object, "END", "SUCCESS",
             f"routes={len(routes)};is_encrypted={metadata_object.get('is_encrypted')};"
             f"required={metadata_object.get('required_chunks')};total={metadata_object.get('chunks')}")

        chunk_retrieval_start = time.perf_counter_ns()
        try:
            async with aiohttp.ClientSession() as session:
                tasks = [DataController.download_chunk(session, route, key_object) for route in routes]
                results = await asyncio.gather(*tasks)
        except Exception as e:
            _log("error", "PULL_CHUNKS", key_object, "END", "ERROR", f"msg={e}")
            return {"error": str(e)}, 500
        chunk_retrieval_end = time.perf_counter_ns()
        _log("debug", "PULL_CHUNKS", key_object, "END", "SUCCESS",
             f"count={len(results)};time_ms={(chunk_retrieval_end - chunk_retrieval_start)/1e6:.3f}")

        object_reconstruction_start = time.perf_counter_ns()
        try:
            if metadata_object["required_chunks"] == 1:
                obj = results[0][1]
                _log("debug", "RECONSTRUCT", key_object, "END", "SIMPLE", f"bytes={len(obj)}")
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
                _log("debug", "RECONSTRUCT", key_object, "END", "EC",
                     f"k={k};n={n};bytes={len(obj)};orig_size={original_size}")
        except Exception as e:
            _log("error", "RECONSTRUCT", key_object, "END", "ERROR", f"msg={e}")
            return {"error": str(e)}, 500
        object_reconstruction_end = time.perf_counter_ns()

        object_caching_start = time.perf_counter_ns()
        try:
            os.makedirs(os.path.dirname(cache_path), exist_ok=True)
            with open(cache_path, "wb") as f:
                f.write(obj)
            os.utime(cache_path, None)
            _log("debug", "CACHE_WRITE", key_object, "END", "SUCCESS",
                 f"path={cache_path};bytes={len(obj)};"
                 f"time_ms={(time.perf_counter_ns()-object_caching_start)/1e6:.3f}")
        except Exception as e:
            _log("warning", "CACHE_WRITE", key_object, "END", "ERROR",
                 f"path={cache_path};msg={e}")

        # timeline
        timeline["Object caching"] = {"start": object_caching_start, "end": time.perf_counter_ns()}
        timeline["Chunk retrieval"] = {"start": chunk_retrieval_start, "end": chunk_retrieval_end}
        timeline["Object reconstruction"] = {"start": object_reconstruction_start, "end": object_reconstruction_end}
        timeline["Metadata retrieval"] = {"start": metadata_retrieval_start, "end": metadata_retrieval_end}

        decode_end = time.perf_counter_ns()
        timeline["pull_end"] = time.time_ns()
        try:
            os.makedirs(os.path.dirname(timeline_path), exist_ok=True)
            with open(timeline_path, "w") as f:
                json.dump(timeline, f, indent=2)
            _log("debug", "TIMELINE_WRITE", key_object, "END", "SUCCESS", f"path={timeline_path}")
        except Exception as e:
            _log("warning", "TIMELINE_WRITE", key_object, "END", "ERROR",
                 f"path={timeline_path};msg={e}")

        _log("debug", "PULL", key_object, "END", "SUCCESS", f"total_decode_ns={decode_end - decode_start}")
        return obj, 200, {
            'Content-Type': 'application/octet-stream',
            "is_encrypted": metadata_object['is_encrypted'],
            "time_decode": decode_end - decode_start
        }

    @staticmethod
    def _resilient_distribution(data_bytes, token_user, metadata_service):
        chunk_start = time.perf_counter_ns()
        _log("debug", "EC_SPLIT", "-", "START", "INIT", f"bytes={len(data_bytes)};user={token_user}")

        servers = requests.get(f"http://{metadata_service}/api/servers/{token_user}").json()
        k, n = 2, 5
        encoder_obj = Encoder(k, n)

        if len(data_bytes) == 0:
            _log("error", "EC_SPLIT", "-", "END", "ERROR", "empty_input")
            raise ValueError("Input file is empty")

        block_size = int(math.ceil(len(data_bytes) / float(k)))
        buf = np.frombuffer(data_bytes, dtype=np.uint8)
        pad_len = block_size * k - len(buf)
        buf = np.pad(buf, (0, pad_len), constant_values=0)
        blocks = [buf[i * block_size:(i + 1) * block_size].tobytes() for i in range(k)]

        fragments = encoder_obj.encode(blocks)
        dt_ms = (time.perf_counter_ns() - chunk_start) / 1e6
        _log("debug", "EC_SPLIT", "-", "END", "SUCCESS",
             f"k={k};n={n};block_size={block_size};fragments={len(fragments)};time_ms={dt_ms:.3f}")

        return fragments, n, k, int(dt_ms * 1e6)

    @staticmethod
    async def _upload_chunk(session, url, data):
        _log("debug", "UPLOAD_CHUNK", "-", "START", "RUN", f"url={url};bytes={len(data)}")
        try:
            async with session.put(url, data=data) as resp:
                if resp.status != 201:
                    _log("error", "UPLOAD_CHUNK", "-", "END", "ERROR",
                         f"url={url};status={resp.status}")
                    raise Exception(f"Upload failed to {url}: {resp.status}")
                _log("debug", "UPLOAD_CHUNK", "-", "END", "SUCCESS", f"url={url};status={resp.status}")
        except Exception as e:
            _log("error", "UPLOAD_CHUNK", "-", "END", "EXCEPTION", f"url={url};msg={e}")
            raise e

    @staticmethod
    def _get_cached_catalog(token_user, catalog):
        return DataController.catalog_cache.get((token_user, catalog))

    @staticmethod
    def _set_cached_catalog(token_user, catalog, catalog_result):
        DataController.catalog_cache[(token_user, catalog)] = catalog_result

    @staticmethod
    def _background_erasure_coding(object_path, key_object, token_user, metadata_service, nodes):
        _log("debug", "PASSIVE_EC", key_object, "START", "INIT",
             f"path={object_path};nodes={len(nodes)}")
        timeline_path = f".temp/{key_object}.timeline.json"
        timeline = {}
        try:
            ec_start = time.time_ns()
            with open(object_path, "rb") as f:
                data_bytes = f.read()

            fragments, n, k, chunk_time = DataController._resilient_distribution(
                data_bytes, token_user, metadata_service
            )
            ec_end = time.time_ns()

            fragment_push_start = time.time_ns()
            for i, fragment in enumerate(fragments):
                url = DataController._http_url(nodes[i]['route'])
                try:
                    with requests.Session() as session:
                        adapter = requests.adapters.HTTPAdapter(
                            max_retries=requests.adapters.Retry(total=3, backoff_factor=0.3)
                        )
                        session.mount('http://', adapter)
                        session.mount('https://', adapter)
                        res = session.put(url, data=fragment)
                        if res.status_code != 201:
                            _log("error", "PASSIVE_EC_PUSH", key_object, "END", "ERROR",
                                 f"frag={i};url={url};status={res.status_code};body={res.text[:256]}")
                        else:
                            _log("debug", "PASSIVE_EC_PUSH", key_object, "END", "SUCCESS",
                                 f"frag={i};url={url};status={res.status_code};bytes={len(fragment)}")
                except Exception as e:
                    _log("error", "PASSIVE_EC_PUSH", key_object, "END", "EXCEPTION",
                         f"frag={i};url={url};msg={e}")

            marker_path = f"{object_path}.pending"
            if os.path.exists(marker_path):
                try:
                    os.remove(marker_path)
                    _log("debug", "PASSIVE_EC_MARKER", key_object, "END", "REMOVED", f"marker={marker_path}")
                except Exception as e:
                    _log("warning", "PASSIVE_EC_MARKER", key_object, "END", "REMOVE_ERROR",
                         f"marker={marker_path};msg={e}")

            fragment_push_end = time.time_ns()
            timeline["erasure_coding"] = {"start": ec_start, "end": ec_end}
            timeline["fragment_push"] = {"start": fragment_push_start, "end": fragment_push_end}
            _log("info", "PASSIVE_EC", key_object, "END", "DONE", f"time_ms={chunk_time/1e6:.2f}")

        except Exception as e:
            _log("error", "PASSIVE_EC", key_object, "END", "ERROR", f"msg={e}")
        finally:
            try:
                with open(timeline_path, "r") as f:
                    existing = json.load(f)
            except Exception:
                existing = {}
            existing.update(timeline)
            try:
                os.makedirs(os.path.dirname(timeline_path), exist_ok=True)
                with open(timeline_path, "w") as f:
                    json.dump(existing, f, indent=2)
                _log("debug", "TIMELINE_WRITE", key_object, "END", "SUCCESS", f"path={timeline_path}")
            except Exception as e:
                _log("warning", "TIMELINE_WRITE", key_object, "END", "ERROR",
                     f"path={timeline_path};msg={e}")

    @staticmethod
    async def upload_metadata(request, token_user, key_object):
        timeline_path = f".temp/{key_object}.timeline.json"
        timeline = {}
        upload_meta_start = time.time_ns()
        _log("debug", "UPLOAD_METADATA", key_object, "START", "INIT", f"user={token_user}")

        metadata_path = f".temp/{key_object}.json"
        data = await request.get_data()
        size = len(data) if data else 0
        try:
            data = data.decode('utf-8')
            os.makedirs(os.path.dirname(metadata_path), exist_ok=True)
            with open(metadata_path, "w") as f:
                json.dump(data, f)
            _log("debug", "UPLOAD_METADATA", key_object, "END", "WRITE_OK", f"path={metadata_path};bytes={size}")
        except Exception as e:
            _log("error", "UPLOAD_METADATA", key_object, "END", "ERROR", f"msg={e}")
            return {"error": str(e)}, 500

        upload_meta_end = time.time_ns()
        timeline["upload_metadata"] = {"start": upload_meta_start, "end": upload_meta_end}

        try:
            with open(timeline_path, "r") as f:
                existing = json.load(f)
        except Exception:
            existing = {}
        existing.update(timeline)
        try:
            os.makedirs(os.path.dirname(timeline_path), exist_ok=True)
            with open(timeline_path, "w") as f:
                json.dump(existing, f, indent=2)
            _log("debug", "TIMELINE_WRITE", key_object, "END", "SUCCESS", f"path={timeline_path}")
        except Exception as e:
            _log("warning", "TIMELINE_WRITE", key_object, "END", "ERROR", f"path={timeline_path};msg={e}")

        _log("debug", "UPLOAD_METADATA", key_object, "END", "SUCCESS", "")
        return {"status": "metadata received"}, 200

    @staticmethod
    async def upload_data(request, metadata_service, pubsub_service, catalog, token_user, key_object, read_time_ns=None):
        _log("debug", "UPLOAD_DATA", key_object, "START", "INIT",
             f"user={token_user};catalog={catalog}")
        timeline_path = f".temp/{key_object}.timeline.json"
        timeline = {}

        timeline["upload_start"] = time.time_ns()
        perf_start = time.perf_counter_ns()

        metadata_path = f".temp/{key_object}.json"
        if not os.path.exists(metadata_path):
            _log("error", "UPLOAD_DATA", key_object, "END", "MISSING_METADATA", f"path={metadata_path}")
            return {"error": "Missing metadata for object"}, 400

        with open(metadata_path) as f:
            request_json = json.load(f)
        if isinstance(request_json, str):
            request_json = json.loads(request_json)

        object_path = f".temp/{key_object}"
        os.makedirs(os.path.dirname(object_path), exist_ok=True)

        # stream file to disk
        stream_start = time.time_ns()
        total_bytes = 0
        try:
            async with aiofiles.open(object_path, "wb") as f:
                async for data in request.body:
                    total_bytes += len(data)
                    await f.write(data)
        except Exception as e:
            _log("error", "UPLOAD_DATA", key_object, "END", "STREAM_ERROR", f"msg={e}")
            return {"error": str(e)}, 500
        stream_end = time.time_ns()
        timeline["stream"] = {"start": stream_start, "end": stream_end}
        _log("debug", "UPLOAD_DATA", key_object, "END", "STREAM_OK",
             f"bytes={total_bytes};time_ms={(stream_end-stream_start)/1e6:.3f}")

        marker_path = f"{object_path}.pending"
        try:
            with open(marker_path, "w") as marker:
                marker.write("pending")
            _log("debug", "UPLOAD_DATA", key_object, "END", "MARKER_WRITTEN", f"marker={marker_path}")
        except Exception as e:
            _log("warning", "UPLOAD_DATA", key_object, "END", "MARKER_WRITE_ERROR",
                 f"marker={marker_path};msg={e}")

        # catalog
        catalog_start = time.time_ns()
        perf_catalog_start = time.perf_counter_ns()
        catalog_result, status = CatalogController.createOrGetCatalog(request, pubsub_service, catalog, token_user)
        perf_catalog_end = time.perf_counter_ns()
        catalog_end = time.time_ns()

        print(status, catalog_result, flush=True)

        timeline["catalog"] = {"start": catalog_start, "end": catalog_end}
        timeline["catalog_perf"] = perf_catalog_end - perf_catalog_start
        if status not in [201, 302]:
            _log("error", "UPLOAD_DATA", key_object, "END", "CATALOG_ERROR", f"status={status};result={catalog_result}")
            return catalog_result, status
        _log("debug", "UPLOAD_DATA", key_object, "END", "CATALOG_OK",
             f"status={status};perf_ms={(perf_catalog_end - perf_catalog_start)/1e6:.3f}")

        token_catalog = catalog_result['data']['tokencatalog']
        request_json['chunks'] = 5
        request_json['required_chunks'] = 2
        request_json['coding_status'] = 'pending'

        metadata_url = f"http://{metadata_service}/api/storage/{token_user}/{token_catalog}/{key_object}"
        metadata_start = time.time_ns()
        try:
            resp = requests.put(metadata_url, json=request_json)
        except Exception as e:
            _log("error", "UPLOAD_DATA", key_object, "END", "METADATA_REGISTER_EXCEPTION",
                 f"url={metadata_url};msg={e}")
            return {"error": str(e)}, 500
        metadata_end = time.time_ns()
        timeline["metadata_register"] = {"start": metadata_start, "end": metadata_end}

        if resp.status_code != 201:
            try:
                body = resp.json()
            except Exception:
                body = {"error": resp.text[:256]}
            _log("error", "UPLOAD_DATA", key_object, "END", "METADATA_REGISTER_ERROR",
                 f"status={resp.status_code};body={body}")
            return body, resp.status_code
        _log("debug", "UPLOAD_DATA", key_object, "END", "METADATA_REGISTER_OK",
             f"status={resp.status_code};time_ms={(metadata_end-metadata_start)/1e6:.3f}")

        nodes = resp.json().get('nodes', [])
        _log("debug", "UPLOAD_DATA", key_object, "END", "NODES_OK", f"count={len(nodes)}")

        # Dispatch EC thread
        ec_thread_start = time.time_ns()
        try:
            thread = threading.Thread(
                target=DataController._background_erasure_coding,
                args=(object_path, key_object, token_user, metadata_service, nodes),
                daemon=True
            )
            thread.start()
            timeline["ec_thread_dispatch"] = {"start": ec_thread_start}
            _log("debug", "UPLOAD_DATA", key_object, "END", "EC_THREAD_DISPATCHED", "")
        except Exception as e:
            _log("warning", "UPLOAD_DATA", key_object, "END", "EC_THREAD_ERROR", f"msg={e}")

        timeline["upload_end"] = time.time_ns()
        total_perf = time.perf_counter_ns() - perf_start
        timeline["upload_total_perf_ns"] = total_perf

        # Regist into catalog
        results, status = CatalogController.registFileInCatalog(
            pubsub_service, catalog_result["data"]["tokencatalog"], token_user, key_object
        )
        print(status, results, flush=True)
        if status != 200:
            _log("warning", "UPLOAD_DATA", key_object, "END", "CATALOG_REGISTER_ERROR",
                 f"status={status};result={results}")
        else:
            _log("debug", "UPLOAD_DATA", key_object, "END", "CATALOG_REGISTER_OK", f"status={status}")

        try:
            os.makedirs(os.path.dirname(timeline_path), exist_ok=True)
            with open(timeline_path, "w") as f:
                json.dump(timeline, f, indent=2)
            _log("debug", "TIMELINE_WRITE", key_object, "END", "SUCCESS", f"path={timeline_path}")
        except Exception as e:
            _log("warning", "TIMELINE_WRITE", key_object, "END", "ERROR",
                 f"path={timeline_path};msg={e}")

        _log("info", "UPLOAD_DATA", key_object, "END", "SUCCESS",
             f"time_upload_ms={total_perf/1e6:.3f}")
        return {
            "key_object": key_object,
            "passive_ec": True,
            "time_upload": round(total_perf / 1e6, 3)
        }, 201
