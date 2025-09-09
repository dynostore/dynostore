import os
import logging

from dynostore.utils.csvlog import make_csv_logger

DATA_CONTAINEER_ID = os.getenv("DATA_CONTAINER_ID")
DC_NAME = f"DATACONTAINER_{DATA_CONTAINEER_ID}"

# ----- logging (safe default; won't double-configure if app sets handlers) -----
_log = make_csv_logger(DC_NAME, __name__) 

# ------------------------------------------------------------------------------

class StorageManager:
    def delete(self, key: str) -> bool:
        pass

    def read(self, key: str) -> bytes:
        pass

    def write(self, key: str, data: bytes) -> bool:
        pass

    def exists(self, key: str) -> bool:
        pass

    def close(self):
        pass


class FileSystemStorage(StorageManager):
    def __init__(self, basepath: str):
        self.basepath = os.path.abspath(basepath)
        os.makedirs(self.basepath, exist_ok=True)
        self.utilization = 0
        _log("INIT", "-", "START", "RUN", f"basepath={self.basepath}")
        _log("INIT", "-", "END", "SUCCESS", "")

    # --- internals ---
    def _full_path(self, key: str) -> str:
        candidate = os.path.abspath(os.path.join(self.basepath, key))
        # prevent traversal
        if not (candidate == self.basepath or candidate.startswith(self.basepath + os.sep)):
            raise ValueError(f"unsafe_key:{key}")
        return candidate

    # --- API ---
    def delete(self, key: str) -> bool:
        _log("DELETE", key, "START", "RUN", "")
        try:
            filepath = self._full_path(key)
            #get size for logging and control
            size = os.path.getsize(filepath)
            os.remove(filepath)
            self.utilization -= size
            _log("DELETE", key, "END", "SUCCESS", f"path={filepath},bytes={size},utilization={self.utilization}")
            return True
        except FileNotFoundError:
            _log("DELETE", key, "END", "NOT_FOUND", "")
            return False
        except Exception as e:
            _log("DELETE", key, "END", "ERROR", f"msg={e}")
            return False

    def read(self, key: str) -> bytes:
        _log("READ", key, "START", "RUN", "")
        filepath = self._full_path(key)
        try:
            with open(filepath, 'rb') as f:
                data = f.read()
            _log("READ", key, "END", "SUCCESS", f"path={filepath};bytes={len(data)}")
            return data
        except Exception as e:
            _log("READ", key, "END", "ERROR", f"path={filepath};msg={e}")
            raise

    def write(self, key: str, data: bytes) -> bool:
        _log("WRITE", key, "START", "RUN", f"bytes={len(data)}")
        filepath = self._full_path(key)
        try:
            os.makedirs(os.path.dirname(filepath), exist_ok=True)
            with open(filepath, 'wb') as f:
                f.write(data)
            self.utilization += len(data)
            _log("WRITE", key, "END", "SUCCESS", f"path={filepath};bytes={len(data)};utilization={self.utilization}")
            return True
        except Exception as e:
            _log("WRITE", key, "END", "ERROR", f"path={filepath};msg={e}")
            return False

    def exists(self, key: str) -> bool:
        _log("EXISTS", key, "START", "RUN", "")
        try:
            filepath = self._full_path(key)
            ok = os.path.exists(filepath)
            _log("EXISTS", key, "END", "SUCCESS", f"path={filepath};exists={int(ok)}")
            return ok
        except Exception as e:
            _log("EXISTS", key, "END", "ERROR", f"msg={e}")
            return False

    def close(self):
        _log("CLOSE", "-", "START", "RUN", "")
        _log("CLOSE", "-", "END", "SUCCESS", "")
