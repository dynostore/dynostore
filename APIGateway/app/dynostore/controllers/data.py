import requests
import json

from dynostore.controllers.catalogs import CatalogController

class DataController():
    
    def deleteObject(
        request,
        tokenUser: str,
        keyObject: str,
        metadaService: str
    ):
        url_to_delete = f'http://{metadaService}/api/storage/{tokenUser}/{keyObject}'
        response = requests.delete(url_to_delete)
        print(response.text, flush=True)
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
        
        if response.status_code >= 500:
            return response.json(), response.status_code
        
        data = response.json()
        routes = data['data']['routes']
        results = []
        
        for route in routes:
            url_node = route['route']
            url_node = f'http://{url_node}'
            response = requests.get(url_node)
            if response.status_code != 200:
                return response.json(), response.status_code
            results.append(response.content)
        
        return results[0], 200, {'Content-Type': 'application/octet-stream'}
    
    def pushData(
        request,
        metadaService: str, 
        pubsubService: str,    
        catalog: str, 
        tokenUser: str,
        keyObject: str
    ):
        files = request.files
        request_json = json.loads(files['json'].read().decode('utf-8'))
        request_bytes = files['data'].read()
        
        results, status_code = CatalogController.createOrGetCatalog(request, pubsubService, catalog, tokenUser)

        
        if status_code == 201 or status_code == 302:
            
            tokenCatalog = results['data']['tokencatalog']
            
            url_to_push_metadata = f'http://{metadaService}/api/storage/{tokenUser}/{tokenCatalog}/{keyObject}'
            
            response = requests.put(url_to_push_metadata, json=request_json)
            
            if response.status_code != 201:
                return response.json(), response.status_code
            
            nodes = response.json()['nodes']
            
            for node in nodes:
                url_node = node['route']
                url_node = f'http://{url_node}'
                response = requests.put(url_node, data=request_bytes)
                if response.status_code != 201:
                    return response.json(), response.status_code
            
            results, code = CatalogController.registFileInCatalog(pubsubService, tokenCatalog, tokenUser, keyObject)

            if code != 201:
                return results, code
            
            return "Objects pushed successfully", response.status_code
        else:
            return results, status_code
        
    

            