import psutil
import os

def get_default_partition_size():
    """Get the size of the default partition where the program is running."""
    current_directory = os.getcwd()
    partition = psutil.disk_partitions(all=False)
    for part in partition:
        if current_directory.startswith(part.mountpoint):
            partition_usage = psutil.disk_usage(part.mountpoint)
            return partition_usage.total
    return None

def get_total_memory():
    """Get the total amount of memory (RAM) in bytes."""
    mem_info = psutil.virtual_memory()
    return mem_info.total