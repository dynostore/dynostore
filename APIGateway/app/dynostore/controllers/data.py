import requests
import json
import pickle

from dynostore.controllers.catalogs import CatalogController
from dynostore.datamanagement.reliability import ida


class DataController():

    def deleteObject(
        request,
        tokenUser: str,
        keyObject: str,
        metadaService: str
    ):
        url_to_delete = f'http://{metadaService}/api/storage/{tokenUser}/{keyObject}'
        response = requests.delete(url_to_delete)
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
        
        print(response.text, flush=True)

        if response.status_code >= 500:
            return response.json(), response.status_code

        data = response.json()
        routes = data['data']['routes']
        results = []
        objectRes = None
        
        for route in routes:
            url_node = route['route']
            url_node = f'http://{url_node}'
            response = requests.get(url_node)
            if response.status_code != 200:
                return response.json(), response.status_code
            results.append(response.content)
            
        
        if len(results) > 1:
            results = [pickle.loads(fragment) for fragment in results]
            objectRes = ida.assemble_bytes(results)
        else:
            objectRes = results[0]

        return objectRes, 200, {'Content-Type': 'application/octet-stream'}

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
        data = []
        if request_json['chunks'] > 1:
            n = request_json['chunks']
            k = request_json['required_chunks']
            data = ida.split_bytes(request_bytes, n, k)
            data = [pickle.dumps(fragment) for fragment in data]
        else:
            data.append(request_bytes)
            

        results, status_code = CatalogController.createOrGetCatalog(
            request, pubsubService, catalog, tokenUser)

        if status_code == 201 or status_code == 302:

            tokenCatalog = results['data']['tokencatalog']

            url_to_push_metadata = f'http://{metadaService}/api/storage/{tokenUser}/{tokenCatalog}/{keyObject}'

            response = requests.put(url_to_push_metadata, json=request_json)
            if response.status_code != 201:
                return response.json(), response.status_code

            nodes = response.json()['nodes']

            for i,node in enumerate(nodes):
                url_node = node['route']
                url_node = f'http://{url_node}'
                response = requests.put(url_node, data=data[i])
                if response.status_code != 201:
                    return response.json(), response.status_code

            results, code = CatalogController.registFileInCatalog(
                pubsubService, tokenCatalog, tokenUser, keyObject)

            if code != 201:
                return results, code

            return "Objects pushed successfully", response.status_code
        else:
            return results, status_code
