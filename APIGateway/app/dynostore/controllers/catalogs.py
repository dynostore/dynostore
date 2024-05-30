import requests

class CatalogController():

    @staticmethod
    def createOrGetCatalog(
        request,
        pubsub: str, 
        catalog: str, 
        tokenuser: str,
        dispersemode: str = "SINGLE",
        encryption: int = 0,
        fathers_token: str = "/",
        processed: int = 0
    ):
        
        url_service = f'http://{pubsub}/catalog/{catalog}/?tokenuser={tokenuser}'
        data = {
            'dispersemode': dispersemode, 
            'encryption': encryption, 
            'fathers_token': fathers_token, 
            'processed': processed
            }
        results = requests.put(url_service, json=data)
        return results.json(), results.status_code
    
    
    @staticmethod
    def deleteCatalog(
        pubsub: str, 
        catalog: str, 
        tokenuser: str
    ):
        url_service = f'http://{pubsub}/catalog/{catalog}/?tokenuser={tokenuser}'
        results = requests.delete(url_service)
        return results.json(), results.status_code
    
    @staticmethod
    def getCatalog(
        pubsub: str, 
        catalog: str, 
        tokenuser: str
    ):
        url_service = f'http://{pubsub}/catalog/{catalog}/?tokenuser={tokenuser}'
        results = requests.get(url_service)
        return results.json(), results.status_code
    
    @staticmethod
    def registFileInCatalog(
        pubsub: str, 
        catalog: str, 
        tokenuser: str, 
        keyObject: str
    ):
        url_service = f'http://{pubsub}/catalog/{catalog}/object/{keyObject}'
        results = requests.post(url_service)
        return results.json(), results.status_code
    
    @staticmethod
    def listFilesInCatalog(
        pubsub: str,
        catalog: str,
        tokenuser: str
    ):
        url_service = f'http://{pubsub}/list/catalog/{catalog}'
        #print(url_service, flush=True)
        results = requests.get(url_service)
        #print(results.text, flush=True)
        return results.json(), results.status_code