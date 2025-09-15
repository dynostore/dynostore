from storage import FileSystemStorage
from dynostore.utils.csvlog import make_csv_logger
import logging
import os
import time

DATA_CONTAINEER_ID = os.getenv("DATA_CONTAINER_ID")
DC_NAME = f"DATACONTAINER_{DATA_CONTAINEER_ID}"

# ----- logging: prefix Status with MEM_ for memory-layer logs -----
_base_log = make_csv_logger(DC_NAME, __name__)

def _log(operation: str, key: str, phase: str, status: str, msg: str = ""):
    # SERVICE, OPERATION, OBJECTKEY, START/END, Status, MSG
    _base_log(operation, key, phase, f"MEM_{status}", msg)

def _t0() -> int:
    return time.perf_counter_ns()

def _ms_since(t_start_ns: int) -> float:
    return (time.perf_counter_ns() - t_start_ns) / 1e6

# ------------------------------------------------------------------------------

class Node:
    def __init__(self, key, value):
        self.key = key
        self.value = value
        self.prev = None
        self.next = None


class LRUCacheStorage:
    def __init__(self, capacity):
        t_total = _t0()
        self.capacity = capacity
        self.utilization = 0
        self.cache = {}
        self.head = Node(None, None)
        self.tail = Node(None, None)
        self.head.next = self.tail
        self.tail.prev = self.head
        self.filesystem = FileSystemStorage('/data/objects')
        _log("INIT", "-", "START", "RUN", f"capacity={self.capacity}")
        _log("INIT", "-", "END", "SUCCESS", f"utilization={self.utilization};time_ms={_ms_since(t_total):.3f}")

    def exists(self, key):
        t_total = _t0()
        _log("EXISTS", key, "START", "RUN", "")
        in_mem = key in self.cache
        if in_mem:
            _log("EXISTS", key, "END", "SUCCESS", f"source=memory;exists=1;time_ms={_ms_since(t_total):.3f}")
            return True
        try:
            t_disk = _t0()
            on_disk = self.filesystem.exists(key)
            _log("EXISTS", key, "END", "SUCCESS",
                 f"source=disk;exists={int(on_disk)};disk_time_ms={_ms_since(t_disk):.3f};total_time_ms={_ms_since(t_total):.3f}")
            return on_disk
        except Exception as e:
            _log("EXISTS", key, "END", "ERROR", f"msg={e};total_time_ms={_ms_since(t_total):.3f}")
            return False

    def evict(self, key):
        t_total = _t0()
        _log("EVICT", key, "START", "RUN", f"utilization={self.utilization};capacity={self.capacity}")
        if key in self.cache:
            node = self.cache[key]
            t_mem = _t0()
            self._remove_node(node)
            try:
                if node.value is not None:
                    self.utilization -= len(node.value)
            except Exception:
                pass
            del self.cache[key]
            _log("EVICT", key, "END", "SUCCESS", f"source=memory;mem_time_ms={_ms_since(t_mem):.3f}")
        try:
            t_disk = _t0()
            self.filesystem.delete(key)
            _log("EVICT", key, "END", "SUCCESS",
                 f"source=disk;disk_time_ms={_ms_since(t_disk):.3f};total_time_ms={_ms_since(t_total):.3f}")
        except Exception as e:
            _log("EVICT", key, "END", "ERROR", f"msg={e};total_time_ms={_ms_since(t_total):.3f}")
            raise Exception("Error deleting from disk. Exception " + str(e))

    def get(self, key):
        t_total = _t0()
        _log("GET", key, "START", "RUN", f"utilization={self.utilization};capacity={self.capacity}")
        if key in self.cache:
            node = self.cache[key]
            t_mv = _t0()
            self._move_to_front(node)
            size = len(node.value) if node.value is not None else 0
            _log("GET", key, "END", "SUCCESS",
                 f"source=memory;bytes={size};move_time_ms={_ms_since(t_mv):.3f};total_time_ms={_ms_since(t_total):.3f}")
            return node.value
        else:
            try:
                t_disk = _t0()
                data = self.filesystem.read(key)
                disk_ms = _ms_since(t_disk)
                bytes_len = len(data) if data is not None else 0
                t_put = _t0()
                self.put(key, data)
                put_ms = _ms_since(t_put)
                _log("GET", key, "END", "SUCCESS",
                     f"source=disk;bytes={bytes_len};disk_time_ms={disk_ms:.3f};put_time_ms={put_ms:.3f};total_time_ms={_ms_since(t_total):.3f}")
                return data
            except Exception as e:
                _log("GET", key, "END", "ERROR", f"msg={e};total_time_ms={_ms_since(t_total):.3f}")
                raise Exception("Error reading from disk.  Exception " + str(e))

    def put(self, key, value):
        t_total = _t0()
        size = len(value) if value is not None else 0
        _log("PUT", key, "START", "RUN", f"bytes={size};utilization={self.utilization};capacity={self.capacity}")
        if key in self.cache:
            node = self.cache[key]
            try:
                old_size = len(node.value) if node.value is not None else 0
                self.utilization += (size - old_size)
            except Exception:
                pass
            node.value = value
            t_mv = _t0()
            self._move_to_front(node)
            _log("PUT", key, "END", "SUCCESS",
                 f"update=1;utilization={self.utilization};move_time_ms={_ms_since(t_mv):.3f};total_time_ms={_ms_since(t_total):.3f}")
        else:
            ev_ms = 0.0
            if self.utilization >= self.capacity:
                t_ev = _t0()
                _log("PUT", key, "START", "EVICT_REQUIRED", f"utilization={self.utilization};capacity={self.capacity}")
                self._evict()
                ev_ms = _ms_since(t_ev)
            node = Node(key, value)
            self.cache[key] = node
            t_add = _t0()
            self._add_to_front(node)
            add_ms = _ms_since(t_add)
            self.utilization += size
            _log("PUT", key, "END", "SUCCESS",
                 f"update=0;utilization={self.utilization};add_time_ms={add_ms:.3f};evict_time_ms={ev_ms:.3f};total_time_ms={_ms_since(t_total):.3f}")

            # write to disk
            try:
                if key is not None:
                    t_wr = _t0()
                    self.filesystem.write(key, value)
                    _log("PUT_WRITE", key, "END", "SUCCESS",
                         f"bytes={size};disk_write_time_ms={_ms_since(t_wr):.3f}")
            except Exception as e:
                _log("PUT_WRITE", key, "END", "ERROR", f"msg={e}")
                raise Exception("Error writing to disk.  Exception " + str(e))

    def _move_to_front(self, node):
        t_total = _t0()
        _log("MOVE_FRONT", node.key, "START", "RUN", "")
        t_rm = _t0()
        self._remove_node(node)
        rm_ms = _ms_since(t_rm)
        t_add = _t0()
        self._add_to_front(node)
        add_ms = _ms_since(t_add)
        _log("MOVE_FRONT", node.key, "END", "SUCCESS",
             f"remove_time_ms={rm_ms:.3f};add_time_ms={add_ms:.3f};total_time_ms={_ms_since(t_total):.3f}")

    def _add_to_front(self, node):
        t_total = _t0()
        _log("ADD_FRONT", node.key, "START", "RUN", "")
        node.prev = self.head
        node.next = self.head.next
        self.head.next.prev = node
        self.head.next = node
        _log("ADD_FRONT", node.key, "END", "SUCCESS", f"total_time_ms={_ms_since(t_total):.3f}")

    def _remove_node(self, node):
        t_total = _t0()
        _log("REMOVE_NODE", node.key, "START", "RUN", "")
        if node.prev is not None:
            node.prev.next = node.next
        if node.next is not None:
            node.next.prev = node.prev
        node.prev = None
        node.next = None
        _log("REMOVE_NODE", node.key, "END", "SUCCESS", f"total_time_ms={_ms_since(t_total):.3f}")

    # remove from memory (and ensure persisted) when capacity exceeded
    def _evict(self):
        t_total = _t0()
        _log("EVICT_INTERNAL", "-", "START", "RUN", f"utilization={self.utilization};capacity={self.capacity}")
        node = self.tail.prev
        if node and node.key is not None:
            try:
                t_wr = _t0()
                self.filesystem.write(node.key, node.value)  # idempotent persist
                _log("EVICT_INTERNAL_WRITE", node.key, "END", "SUCCESS",
                     f"bytes={len(node.value) if node.value else 0};disk_write_time_ms={_ms_since(t_wr):.3f}")
            except Exception as e:
                _log("EVICT_INTERNAL_WRITE", node.key, "END", "ERROR", f"msg={e}")
            try:
                size = len(node.value) if node.value is not None else 0
                self.utilization -= size
            except Exception:
                size = 0
            t_rm = _t0()
            self._remove_node(node)
            rm_ms = _ms_since(t_rm)
            del self.cache[node.key]
            _log("EVICT_INTERNAL", node.key, "END", "SUCCESS",
                 f"freed_bytes={size};remove_time_ms={rm_ms:.3f};utilization={self.utilization};total_time_ms={_ms_since(t_total):.3f}")
        else:
            _log("EVICT_INTERNAL", "-", "END", "SUCCESS", f"no_candidate;total_time_ms={_ms_since(t_total):.3f}")
