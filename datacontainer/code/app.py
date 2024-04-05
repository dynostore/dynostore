# Data container API

import os
from flask import Flask, jsonify, request
import requests
from werkzeug.utils import secure_filename

from dynostore.decorators.token import validateToken
from caching import LRUCacheStorage

app = Flask(__name__)
app.config["DEBUG"] = True

AUTH_HOST = os.getenv('AUTH_HOST')
URL_AUTH = "http://" + AUTH_HOST + '/auth/v1/user?tokenuser='

storage = LRUCacheStorage(100000)

#check if node is alive
@app.route('/health', methods=["GET"])
def health():
    return jsonify({"message": "Data container is alive"}), 200

@app.route('/objects/<objectkey>/<tokenuser>', methods=["PUT"])
@validateToken(auth_host=AUTH_HOST)
def upload_object(objectkey, tokenuser):
    if request.method == 'PUT':
        bytes_ = request.data
        try:
            storage.put(objectkey, bytes_)
            return jsonify({"message": "Data successfully uploaded"}), 201
        except Exception as e:
            return jsonify({"error": "Error uploading data. Exception " + str(e)}), 500

    else:
        return jsonify({"error": "Invalid request method"}), 405


@app.route('/objects/<objectkey>/<tokenuser>', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
def download_object(objectkey, tokenuser):
    if request.method == 'GET':
        try:
            return storage.get(objectkey), 200
        except Exception as e:
            return jsonify({"error": "Error downloading data. Exception " + str(e)}), 500
    else:
        return jsonify({"error": "Invalid request method"}), 405


@app.route('/objects/<objectkey>/<tokenuser>', methods=["DELETE"])
@validateToken(auth_host=AUTH_HOST)
def delete_object(objectkey, tokenuser):
    if request.method == 'DELETE':
        try:
            storage.evict(objectkey)
            return jsonify({"message": "Data successfully deleted"}), 200
        except Exception as e:
            return jsonify({"error": "Error deleting data. Exception " + str(e)}), 500
    else:
        return jsonify({"error": "Invalid request method"}), 405


@app.route('/objects/<objectkey>/<tokenuser>', methods=["HEAD"])
@validateToken(auth_host=AUTH_HOST)
def head_object(objectkey, tokenuser):
    if request.method == 'HEAD':
        if storage.exists(objectkey):
            return jsonify({"message": "Data exists"}), 200
        else:
            return jsonify({"error": "Data does not exist."}), 404
    else:
        return jsonify({"error": "Invalid request method"}), 405


if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0')
