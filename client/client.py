import requests
import uuid
from utils.data import chunk_bytes
from constants import MAX_CHUNK_LENGTH
from reliability.ida import split_bytes
from concurrent.futures import ProcessPoolExecutor
import pickle
import time
import hashlib
import io
import json
from nfrs.compress import ObjectCompressor

class Client(object):

    def __init__(self, metadata_server):
        self.metadata_server = metadata_server
        self.object_compressor = ObjectCompressor()

    def evict(
        self,
        key: str,
        token_user: str = None,
        session: requests.Session = None
    ) -> None:
        delete_ = requests.delete if session is None else session.delete
        response = delete_(
            f'http://{self.metadata_server}/storage/{token_user}/{key}'
        )

        if not response.ok:
            raise requests.exceptions.RequestException(
                f'Server returned HTTP error code {response.status_code}. '
                f'{response.text}',
                response=response,
            )

    def exists(
        self,
        key: str,
        token_user: str = None,
        session: requests.Session = None
    ) -> bool:

        get_ = requests.get if session is None else session.get
        response = get_(
            f'http://{self.metadata_server}/storage/{token_user}/{key}/exists'
        )
        if not response.ok:
            raise requests.exceptions.RequestException(
                f'Server returned HTTP error code {response.status_code}. '
                f'{response.text}',
                response=response,
            )

        return response.json()["exists"]

    def get(
        self,
        key: str,
        token_user: str = None,
        session: requests.Session = None
    ) -> bytes:
        get = requests.get if session is None else session.get
        # print(key, type(key), sep=" - ")
        response = get(
            f'http://{self.metadata_server}/storage/{token_user}/{key}'
        )

        print(response.text)

        if response.status_code == 404:
            raise requests.exceptions.RequestException(
                f'DynoStore returned HTTP error code {response.status_code}. '
                f'{response.text}',
                response=response,
            )

        if response.status_code == 200:

            data = bytearray()
            for chunk in response.iter_content(chunk_size=None):
                data += chunk
            # print(data)
            return bytes(data)

    def put(
        self,
        data: bytes,
        token_user: str,
        catalog: str,
        key: str = str(uuid.uuid4()),
        name: str = None,
        session: requests.Session = None,
        is_encrypted: bool = False,
        max_workers: int = 1,
        resiliency: int = 0,
        number_of_chunks=1,
        required_chunks=1,
        nodes=None
    ) -> None:
        start_time = time.perf_counter_ns()
        data_hash = hashlib.sha3_256(data).hexdigest()
        name = data_hash if name is None else name

        put = requests.put if session is None else session.put
        data_compressed = self.object_compressor.compress(data)
        fake_file = io.BytesIO(data_compressed)

        payload = {"name": name, "size": len(data_compressed), "hash": data_hash, "key": key,
                   "is_encrypted": int(is_encrypted), "resiliency": resiliency,
                   "chunks": number_of_chunks, "required_chunks": required_chunks,
                   "nodes": nodes}
        files = [
            ('json', ('payload.json', json.dumps(payload), 'application/json')),
            ('data', ('data.bin', fake_file, 'application/octet-stream'))
        ]
        response = requests.put(
            f'http://{self.metadata_server}/storage/{token_user}/{catalog}/{key}', files=files)

        if response.status_code == 201:
            res = response.json()
        else:
            raise requests.exceptions.RequestException(
                f'Metadata server returned HTTP error code {response.status_code}. '
                f'{response.text}',
                response=response,
            )
        end = time.perf_counter_ns()
        return {
            "total_time": (end - start_time) / 1e6, 
            "metadata_time": res["total_time"] / 1e6, 
            "upload_time": res["time_upload"] / 1e6
        }

    