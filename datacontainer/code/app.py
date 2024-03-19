#Data container API

import os
from flask import Flask, jsonify, request
import requests
from werkzeug.utils import secure_filename

from caching import LRUCacheStorage

app = Flask(__name__)
app.config["DEBUG"] = True

AUTH_HOST = os.getenv('AUTH_HOST')
URL_AUTH = "http://" + AUTH_HOST + '/auth/v1/user?tokenuser='

storage = LRUCacheStorage(100000)


@app.route('/objects/<objectkey>/<usertoken>', methods=["PUT"])
def upload_object(objectkey, usertoken):
    if request.method == 'PUT':
        
        url = URL_AUTH + usertoken
        response = requests.get(url)
        if response.status_code != 200:
            return jsonify({"error": "Invalid user token"}), 401
        
        bytes_ = request.data 
        try:
            storage.put(objectkey, bytes_)
            return jsonify({"message": "Data successfully uploaded"}), 201     
        except Exception as e:
            return jsonify({"error": "Error uploading data. Exception " + str(e)}), 500      
        
    else:
        return jsonify({"error": "Invalid request method"}), 405
    
@app.route('/objects/<objectkey>/<usertoken>', methods=["GET"])
def download_object(objectkey, usertoken):
    if request.method == 'GET':
        
        url = URL_AUTH + usertoken
        response = requests.get(url)
        if response.status_code != 200:
            return jsonify({"error": "Invalid user token"}), 401
        
        try:
            return storage.get(objectkey), 200
        except Exception as e:
            return jsonify({"error": "Error downloading data. Exception " + str(e)}), 500
    else:
        return jsonify({"error": "Invalid request method"}), 405

@app.route('/objects/<objectkey>/<usertoken>', methods=["DELETE"])
def delete_object(objectkey, usertoken):
    if request.method == 'DELETE':
        
        url = URL_AUTH + usertoken
        response = requests.get(url)
        if response.status_code != 200:
            return jsonify({"error": "Invalid user token"}), 401
        
        try:
            storage.evict(objectkey)
            return jsonify({"message": "Data successfully deleted"}), 200
        except Exception as e:
            return jsonify({"error": "Error deleting data. Exception " + str(e)}), 500
    else:
        return jsonify({"error": "Invalid request method"}), 405

@app.route('/objects/<objectkey>/<usertoken>', methods=["HEAD"])
def head_object(objectkey, usertoken):
    if request.method == 'HEAD':
        
        url = URL_AUTH + usertoken
        response = requests.get(url)
        if response.status_code != 200:
            return jsonify({"error": "Invalid user token"}), 401
        
        if storage.exists(objectkey):
            return jsonify({"message": "Data exists"}), 200
        else:
            return jsonify({"error": "Data does not exist. Exception " + str(e)}), 404
    else:
        return jsonify({"error": "Invalid request method"}), 405



if __name__ == '__main__':
   app.run(debug = True, host='0.0.0.0')