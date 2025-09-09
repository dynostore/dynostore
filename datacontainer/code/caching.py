from storage import FileSystemStorage
from dynostore.utils.csvlog import make_csv_logger
import logging
import os

DATA_CONTAINEER_ID = os.getenv("DATA_CONTAINER_ID")
DC_NAME = f"DATACONTAINER_{DATA_CONTAINEER_ID}"

# ----- logging (safe default; won't double-configure if app sets handlers) -----

_log = make_csv_logger(DC_NAME, __name__) 

# ------------------------------------------------------------------------------


class Node:
    def __init__(self, key, value):
        self.key = key
        self.value = value
        self.prev = None
        self.next = None


class LRUCacheStorage:
    def __init__(self, capacity):
        self.capacity = capacity
        self.utilization = 0
        self.cache = {}
        self.head = Node(None, None)
        self.tail = Node(None, None)
        self.head.next = self.tail
        self.tail.prev = self.head
        self.filesystem = FileSystemStorage('/data/objects')
        _log("INIT", "-", "START", "RUN", f"capacity={self.capacity}")
        _log("INIT", "-", "END", "SUCCESS", f"utilization={self.utilization}")

    def exists(self, key):
        _log("EXISTS", key, "START", "RUN", "")
        in_mem = key in self.cache
        if in_mem:
            _log("EXISTS", key, "END", "SUCCESS", "source=memory;exists=1")
            return True
        try:
            on_disk = self.filesystem.exists(key)
            _log("EXISTS", key, "END", "SUCCESS", f"source=disk;exists={int(on_disk)}")
            return on_disk
        except Exception as e:
            _log("EXISTS", key, "END", "ERROR", f"msg={e}")
            return False

    def evict(self, key):
        _log("EVICT", key, "START", "RUN", f"utilization={self.utilization};capacity={self.capacity}")
        if key in self.cache:
            node = self.cache[key]
            self._remove_node(node)
            try:
                # Adjust utilization only for in-memory removal
                if node.value is not None:
                    self.utilization -= len(node.value)
            except Exception:
                pass
            del self.cache[key]
            _log("EVICT", key, "END", "SUCCESS", "source=memory")
        try:
            self.filesystem.delete(key)
            _log("EVICT", key, "END", "SUCCESS", "source=disk")
        except Exception as e:
            _log("EVICT", key, "END", "ERROR", f"msg={e}")
            raise Exception("Error deleting from disk. Exception " + str(e))

    def get(self, key):
        _log("GET", key, "START", "RUN", f"utilization={self.utilization};capacity={self.capacity}")
        if key in self.cache:
            node = self.cache[key]
            self._move_to_front(node)
            size = len(node.value) if node.value is not None else 0
            _log("GET", key, "END", "SUCCESS", f"source=memory;bytes={size}")
            return node.value
        else:
            # read from disk
            try:
                data = self.filesystem.read(key)
                bytes_len = len(data) if data is not None else 0
                self.put(key, data)
                _log("GET", key, "END", "SUCCESS", f"source=disk;bytes={bytes_len}")
                return data
            except Exception as e:
                _log("GET", key, "END", "ERROR", f"msg={e}")
                raise Exception("Error reading from disk.  Exception " + str(e))

    def put(self, key, value):
        size = len(value) if value is not None else 0
        _log("PUT", key, "START", "RUN", f"bytes={size};utilization={self.utilization};capacity={self.capacity}")
        if key in self.cache:
            node = self.cache[key]
            # Adjust utilization for new size versus old size (keeps logic consistent)
            try:
                old_size = len(node.value) if node.value is not None else 0
                self.utilization += (size - old_size)
            except Exception:
                pass
            node.value = value
            self._move_to_front(node)
            _log("PUT", key, "END", "SUCCESS", f"update=1;utilization={self.utilization}")
        else:
            if self.utilization >= self.capacity:
                _log("PUT", key, "START", "EVICT_REQUIRED", f"utilization={self.utilization};capacity={self.capacity}")
                self._evict()
            node = Node(key, value)
            self.cache[key] = node
            self._add_to_front(node)
            self.utilization += size
            _log("PUT", key, "END", "SUCCESS", f"update=0;utilization={self.utilization}")

            # write to disk
            try:
                if key is not None:
                    self.filesystem.write(key, value)
                    _log("PUT_WRITE", key, "END", "SUCCESS", f"bytes={size}")
            except Exception as e:
                _log("PUT_WRITE", key, "END", "ERROR", f"msg={e}")
                raise Exception("Error writing to disk.  Exception " + str(e))

    def _move_to_front(self, node):
        _log("MOVE_FRONT", node.key, "START", "RUN", "")
        self._remove_node(node)
        self._add_to_front(node)
        _log("MOVE_FRONT", node.key, "END", "SUCCESS", "")

    def _add_to_front(self, node):
        _log("ADD_FRONT", node.key, "START", "RUN", "")
        node.prev = self.head
        node.next = self.head.next
        self.head.next.prev = node
        self.head.next = node
        _log("ADD_FRONT", node.key, "END", "SUCCESS", "")

    def _remove_node(self, node):
        _log("REMOVE_NODE", node.key, "START", "RUN", "")
        if node.prev is not None:
            node.prev.next = node.next
        if node.next is not None:
            node.next.prev = node.prev
        node.prev = None
        node.next = None
        _log("REMOVE_NODE", node.key, "END", "SUCCESS", "")

    # remove from memory (and ensure persisted) when capacity exceeded
    def _evict(self):
        _log("EVICT_INTERNAL", "-", "START", "RUN", f"utilization={self.utilization};capacity={self.capacity}")
        node = self.tail.prev
        if node and node.key is not None:
            try:
                # persist latest to disk (idempotent write)
                self.filesystem.write(node.key, node.value)
                _log("EVICT_INTERNAL_WRITE", node.key, "END", "SUCCESS", f"bytes={len(node.value) if node.value else 0}")
            except Exception as e:
                _log("EVICT_INTERNAL_WRITE", node.key, "END", "ERROR", f"msg={e}")
                # still proceed with memory eviction to free space

            try:
                size = len(node.value) if node.value is not None else 0
                self.utilization -= size
            except Exception:
                pass
            self._remove_node(node)
            del self.cache[node.key]
            _log("EVICT_INTERNAL", node.key, "END", "SUCCESS", f"freed_bytes={size};utilization={self.utilization}")
        else:
            _log("EVICT_INTERNAL", "-", "END", "SUCCESS", "no_candidate")
