import os

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
        self.basepath = basepath
        
    def delete(self, key: str) -> bool:
        filepath = os.path.join(self.basepath, key)
        try:
            os.remove(filepath)
            return True
        except Exception as e:
            return False
        
    def read(self, key: str) -> bytes:
        filepath = os.path.join(self.basepath, key)
        with open(filepath, 'rb') as f:
            return f.read()
    
    def write(self, key: str, data: bytes) -> bool:
        filepath = os.path.join(self.basepath, key)
        try:
            with open(filepath, 'wb') as f:
                f.write(data)
                return True
        except Exception as e:
            return False
    
    def exists(self, key: str) -> bool:
        filepath = os.path.join(self.basepath, key)
        return os.path.exists(filepath)
    
    def close(self):
        pass
    