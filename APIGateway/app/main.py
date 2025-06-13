import os
from flask import Flask, jsonify, request, render_template, redirect, flash, url_for, render_template_string, session
from flask_login import LoginManager, login_user, logout_user, UserMixin


import requests
from uuid import uuid4
import time
import json
from dynostore.controllers.auth import AuthController
from dynostore.decorators.token import validateToken, validateAdminToken
from dynostore.controllers.catalogs import CatalogController
from dynostore.controllers.data import DataController
from dynostore.controllers.datacontainer import DataContainerController

from dynostore.models.user import User
from dynostore.models.device_code import DeviceCode
from dynostore.db import db

app = Flask(__name__)
app.config["DEBUG"] = True
app.secret_key = 'supersecret'
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///db.sqlite'

db.init_app(app)

# === Flask-Login setup ===
login_manager = LoginManager()
login_manager.init_app(app)
login_manager.login_view = "login"

AUTH_HOST = os.getenv('AUTH_HOST')
PUB_SUB_HOST = os.getenv('PUB_SUB_HOST')
METADATA_HOST = os.getenv('METADATA_HOST')


@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))


# === Endpoint: CLI gets a device code ===

@app.route("/device/code", methods=["POST"])
def device_code():
    device_code = str(uuid4())
    user_code = str(uuid4())[:8].upper()
    expires_at = int(time.time()) + 600

    dc = DeviceCode(
        device_code=device_code,
        user_code=user_code,
        expires_at=expires_at
    )
    
    db.session.add(dc)
    db.session.commit()

    return jsonify({
        "device_code": device_code,
        "user_code": user_code,
        "verification_uri": "http://localhost:8095/device",
        "expires_in": 600,
        "interval": 5
    })

# === Endpoint: User visits this to enter the code ===


@app.route("/device", methods=["GET", "POST"])
def device_entry():
    if request.method == "GET":
        return render_template_string('''
            <h2>Device Verification</h2>
            <form method="post">
                <label>Enter your code:</label><br>
                <input name="user_code" required><br>
                <input type="submit" value="Continue">
            </form>
        ''')

    user_code = request.form.get("user_code")
    device = DeviceCode.query.filter_by(user_code=user_code).first()

    if not device:
        return "Invalid code", 400
    
    session["device_code"] = device.device_code

    return render_template_string('''
        <h2>Login</h2>
        <form method="post" action="/auth/user/login?type=device">
            <input type="hidden" name="device_code" value="{{ device_code }}">
            <label>Username:</label><br>
            <input name="username" required><br>
            <label>Password:</label><br>
            <input type="password" name="password" required><br><br>
            <input type="submit" value="Login">
        </form>
    ''', device_code=device.device_code)

# === Endpoint: CLI polls for token ===


@app.route("/token/validate", methods=["POST"])
def verify_token():
    data = request.json or request.form
    token = data.get("token")

    if not token:
        return jsonify({"error": "missing_token"}), 400

    token_entry = AccessToken.query.filter_by(token=token).first()
    now = int(time.time())
    if token_entry and token_entry.expires_at > now:
        # Token is valid, get all user information
        user = User.query.filter_by(username=token_entry.username).first()
        if not user:
            return jsonify({"error": "user_not_found"}), 404
        
        return jsonify(user.to_dict()), 200

    return jsonify({"error": "invalid_or_expired_token"}), 401

# === Protected API example ===


@app.route("/me")
def profile():
    auth = request.headers.get("Authorization")
    if not auth or not auth.startswith("Bearer "):
        return jsonify({"error": "missing_token"}), 401

    token = auth.split()[1]
    token_entry = AccessToken.query.filter_by(token=token).first()
    now = int(time.time())
    if not token_entry or token_entry.expires_at <= now:
        return jsonify({"error": "invalid_or_expired_token"}), 401

    return jsonify({"username": token_entry.username, "token": token})


# Authentication service routes
@app.route('/auth/organization/<name>/<acronym>', methods=["PUT"])
def createOrganization(name, acronym):
    """
    Route to create an organization
    """
    url_service = f'http://{AUTH_HOST}/auth/v1/hierarchy'
    data = {"option": "NEW", "fullname": name, "acronym": acronym,
            "fathers_token": request.json['fathers_token']}
    results = requests.post(url_service, json=data)
    return jsonify(results.json()), results.status_code





@app.route('/auth/organization/<name>/<acronym>', methods=["GET"])
def getOrganization(name, acronym):
    """
    Route to get the metadata of an organization
    """
    url_service = f'http://{AUTH_HOST}/auth/v1/hierarchy'
    data = {"option": "CHECK", "fullname": name, "acronym": acronym}
    results = requests.post(url_service, json=data)
    return jsonify(results.json()), results.status_code


"""
Route to get the list of organizations
"""


@app.route('/auth/organization', methods=["GET"])
def getOrganizations():
    url_service = f'http://{AUTH_HOST}/auth/v1/hierarchy/all/'
    results = requests.get(url_service)
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


# === Routes ===
@app.route("/auth/user/login", methods=["GET", "POST"])
def login():

    if request.method == "GET":
        return render_template("login.html")

    inputs = None
    if request.is_json:
        inputs = request.json
    else:
        inputs = request.form

    if not "username" in inputs or not "password" in inputs:
        return "Missing username or password", 400

    payload = {
        "user": inputs["username"],
        "password": inputs["password"]
    }
    resp = requests.post(
        f"http://{AUTH_HOST}/auth/v1/users/login/", json=payload)

    if resp.status_code == 200:
        user_data = resp.json()["data"]

        if "type" in request.args and request.args["type"] == "device":
            device_code = request.form.get("device_code")
            device = DeviceCode.query.filter_by(device_code=device_code).first()
            if not device:
                return "Invalid session", 400

            token = str(uuid4())
            expires_at = int(time.time()) + 3600  # Token valid for 1 hour

            device.verified = True
            device.username = inputs["username"]
            db.session.add(AccessToken(token=token, username=inputs["username"], expires_at=expires_at))
            db.session.commit()

            # Store user credentials
            user = User.query.filter_by(username=user_data["username"]).first()
            if not user:
                user = User(
                    username=user_data["username"],
                    access_token=user_data["access_token"],
                    user_token=user_data["tokenuser"],
                    api_key=user_data["apikey"]
                )
                db.session.add(user)
                db.session.commit()

            return render_template_string(f'''
                <h2>Authentication Successful</h2>
                <p>Copy and paste this token into your CLI application:</p>
                <code>{token}</code>
            ''')

        # If no next parameter, just return the user data)
        return resp.json(), 200

    return "Invalid credentials", 401


@app.route("/logout")
def logout():
    logout_user()
    return redirect("/")


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


@app.route('/pubsub/<tokenuser>/catalog/<catalog>', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
def getCatalog(tokenuser, catalog):
    """
    Route to get the metadata of a catalog
    """
    return CatalogController.getCatalog(PUB_SUB_HOST, catalog, tokenuser)

@app.route('/pubsub/<tokenuser>/catalog/<catalog>', methods=["DELETE"])
@validateToken(auth_host=AUTH_HOST)
def deleteCatalog(tokenuser, catalog):
    """
    Route to delete a catalog
    """
    url_service = f'http://{PUB_SUB_HOST}/catalog/{catalog}/?tokenuser={tokenuser}'
    results = requests.delete(url_service)
    return jsonify(results.json()), results.status_code

@app.route('/pubsub/<tokenuser>/catalog/<catalog>/list', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
def listCatalogFiles(tokenuser, catalog):
    """
    List files in catalog
    """
    return CatalogController.listFilesInCatalog(PUB_SUB_HOST, catalog, tokenuser)


# Storage service routes
@app.route('/storage/<tokenuser>/<keyobject>', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
def downloadObject(tokenuser, keyobject):
    """
    Route to download an object
    """
    return DataController.pullData(request, tokenuser, keyobject, METADATA_HOST, PUB_SUB_HOST)

# DREX Experiments (temporal functions)
@app.route('/storage/<tokenuser>/<catalog>/<keyobject>', methods=["PUT"])
@validateToken(auth_host=AUTH_HOST)
def uploadObjectDREX(tokenuser, catalog, keyobject):
    # print("NEW", tokenuser, catalog, keyobject, flush=True)
    return DataController.pushDataDRex(request, METADATA_HOST, PUB_SUB_HOST, catalog, tokenuser, keyobject)

@app.route('/storage/<tokenuser>/<keyobject>/exists', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
def existsObject(tokenuser, keyobject):
    """
    Route to check if an object exists
    """
    return DataController.existsObject(request, tokenuser, keyobject, METADATA_HOST)

@app.route('/storage/<tokenuser>/<keyobject>', methods=["DELETE"])
@validateToken(auth_host=AUTH_HOST)
def deleteObject(tokenuser, keyobject):
    """
    Route to delete an object
    """
    return DataController.deleteObject(request, tokenuser, keyobject, METADATA_HOST)

# Data containers management
@app.route('/datacontainer/<admintoken>', methods=["POST"])
@validateAdminToken(auth_host=AUTH_HOST)
def registDataContainer(admintoken):
    """
    Route to regist a data container on the metadata service
    """
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
    # print(url_service, flush=True)
    results = requests.get(url_service)
    # print(results.text, flush=True)
    return jsonify(results.json()), results.status_code


@app.route("/clean/<admintoken>", methods=["GET"])
@validateAdminToken(auth_host=AUTH_HOST)
def clean(admintoken):
    url_service = f'http://{METADATA_HOST}/api/clean'
    results = requests.get(url_service)
    print(results.text, flush=True)
    return jsonify(results.json()), results.status_code


if __name__ == '__main__':
    with app.app_context():
        db.create_all()
    app.run(debug=True, host='0.0.0.0', port=80)
