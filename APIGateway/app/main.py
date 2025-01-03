import os
from flask import Flask, jsonify, request
import requests
from werkzeug.utils import secure_filename

from dynostore.controllers.auth import AuthController
from dynostore.decorators.token import validateToken, validateAdminToken
from dynostore.controllers.catalogs import CatalogController
from dynostore.controllers.data import DataController
from dynostore.controllers.datacontainer import DataContainerController

from drex.utils.prediction import Predictor

app = Flask(__name__)
app.config["DEBUG"] = True

AUTH_HOST = os.getenv('AUTH_HOST')
PUB_SUB_HOST = os.getenv('PUB_SUB_HOST')
METADATA_HOST = os.getenv('METADATA_HOST')

predictor = Predictor()  # Update for different file sizes

# Authentication service routes
"""
Route to create an organization
"""
@app.route('/auth/organization/<name>/<acronym>', methods=["PUT"])
def createOrganization(name, acronym):
    url_service = f'http://{AUTH_HOST}/auth/v1/hierarchy'
    data = {"option": "NEW", "fullname": name, "acronym": acronym,
            "fathers_token": request.json['fathers_token']}
    results = requests.post(url_service, json=data)
    return jsonify(results.json()), results.status_code


"""
Route to get the metadata of an organization
"""
@app.route('/auth/organization/<name>/<acronym>', methods=["GET"])
def getOrganization(name, acronym):
    url_service = f'http://{AUTH_HOST}/auth/v1/hierarchy'
    data = {"option": "CHECK", "fullname": name, "acronym": acronym}
    results = requests.post(url_service, json=data)
    return jsonify(results.json()), results.status_code


"""
Route to regist an user
"""
@app.route('/auth/user', methods=["POST"])
def createUser():
    url_service = f'http://{AUTH_HOST}/auth/v1/users/create'
    data = {
        "option": "NEW",
        "email": request.json['email'],
        "password": request.json['password'],
        "username": request.json['username'],
        "tokenorg": request.json['tokenorg']
    }
    results = requests.post(url_service, json=data)
    return jsonify(results.json()), results.status_code


"""
Route to get the metadata of an user
"""
@app.route('/auth/user/<tokenuser>', methods=["GET"])
def validateUsertToken(tokenuser):
    return AuthController.validateUsertToken(tokenuser, AUTH_HOST)


# PubSub service routes

"""
Route to create a catalog
"""
@app.route('/pubsub/<tokenuser>/catalog/<catalogname>', methods=["PUT"])
@validateToken(auth_host=AUTH_HOST)
def createCatalog(tokenuser, catalogname):
    # validate inputs
    dispersemode = request.json['dispersemode'] if 'dispersemode' in request.json else "SINGLE"
    encryption = request.json['encryption'] if 'encryption' in request.json else 0
    fathers_token = request.json['fathers_token'] if 'fathers_token' in request.json else "/"
    processed = request.json['processed'] if 'processed' in request.json else 0

    return CatalogController.createOrGetCatalog(
        request,
        PUB_SUB_HOST,
        catalogname,
        tokenuser,
        dispersemode,
        encryption,
        fathers_token,
        processed
    )


"""
Route to get the metadata of a catalog
"""
@app.route('/pubsub/<tokenuser>/catalog/<catalog>', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
def getCatalog(tokenuser, catalog):
    return CatalogController.getCatalog(PUB_SUB_HOST, catalog, tokenuser)


"""
Route to delete a catalog
"""
@app.route('/pubsub/<tokenuser>/catalog/<catalog>', methods=["DELETE"])
@validateToken(auth_host=AUTH_HOST)
def deleteCatalog(tokenuser, catalog):
    url_service = f'http://{PUB_SUB_HOST}/catalog/{catalog}/?tokenuser={tokenuser}'
    results = requests.delete(url_service)
    return jsonify(results.json()), results.status_code


"""
List files in catalog
"""
@app.route('/pubsub/<tokenuser>/catalog/<catalog>/list', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
def listCatalogFiles(tokenuser, catalog):
    return CatalogController.listFilesInCatalog(PUB_SUB_HOST, catalog, tokenuser)

# Storage service routes

"""
Route to upload an object
"""
@app.route('/storage/<tokenuser>/<catalog>/<keyobject>', methods=["PUT"])
@validateToken(auth_host=AUTH_HOST)
def uploadObject(tokenuser, catalog, keyobject):
    #print("NEW", tokenuser, catalog, keyobject, flush=True)
    return DataController.pushData(request, METADATA_HOST, PUB_SUB_HOST, catalog, tokenuser, keyobject, predictor)


"""
Route to download an object
"""
@app.route('/storage/<tokenuser>/<keyobject>', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
def downloadObject(tokenuser, keyobject):
    return DataController.pullData(request, tokenuser, keyobject, METADATA_HOST, PUB_SUB_HOST)

# DREX Experiments (temporal functions)
@app.route('/drex/storage/<tokenuser>/<catalog>/<keyobject>', methods=["PUT"])
@validateToken(auth_host=AUTH_HOST)
def uploadObjectDREX(tokenuser, catalog, keyobject):
    #print("NEW", tokenuser, catalog, keyobject, flush=True)
    return DataController.pushDataDRex(request, METADATA_HOST, PUB_SUB_HOST, catalog, tokenuser, keyobject)


"""
Route to check if an object exists
"""
@app.route('/storage/<tokenuser>/<keyobject>/exists', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
def existsObject(tokenuser, keyobject):
    return DataController.existsObject(request, tokenuser, keyobject, METADATA_HOST)


"""
Route to delete an object
"""
@app.route('/storage/<tokenuser>/<keyobject>', methods=["DELETE"])
@validateToken(auth_host=AUTH_HOST)
def deleteObject(tokenuser, keyobject):
    return DataController.deleteObject(request, tokenuser, keyobject, METADATA_HOST)

# Data containers management


"""
Route to regist a data container on the metadata service
"""
@app.route('/datacontainer/<admintoken>', methods=["POST"])
@validateAdminToken(auth_host=AUTH_HOST)
def registDataContainer(admintoken):
    return DataContainerController.regist(request, admintoken, METADATA_HOST)

@app.route('/datacontainer/<admintoken>/all', methods=["DELETE"])
@validateAdminToken(auth_host=AUTH_HOST)
def deleteAllDCs(admintoken):
    return DataContainerController.delete_all(request, admintoken, METADATA_HOST)

# Development
@app.route("/statistic/<admintoken>", methods=["GET"])
@validateAdminToken(auth_host=AUTH_HOST)
def statistics(admintoken):
    url_service = f"http://{METADATA_HOST}/api/servers/{admintoken}"
    #print(url_service, flush=True)
    results = requests.get(url_service)
    #print(results.text, flush=True)
    return jsonify(results.json()), results.status_code

@app.route("/clean/<admintoken>", methods=["GET"])
@validateAdminToken(auth_host=AUTH_HOST)
def clean(admintoken):
    url_service = f'http://{METADATA_HOST}/api/clean'
    results = requests.get(url_service)
    print(results.text, flush=True)
    return jsonify(results.json()), results.status_code

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=80)
