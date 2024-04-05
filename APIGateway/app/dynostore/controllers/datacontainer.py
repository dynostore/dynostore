import requests
import json


class DataContainerController():
    def regist(request, admintoken: str, metadataService: str):
        url_to_regist = f"http://{metadataService}/api/servers/{admintoken}"
        print(url_to_regist, flush=True)
        r = requests.post(url_to_regist, data=request.json)
        print(r.status_code, flush=True)
        print(r.text, flush=True)
        return r.text
        #return r.json(), r.status_code
