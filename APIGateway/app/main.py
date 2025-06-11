import os
from flask import Flask, jsonify, request, render_template, redirect, render_template_string
from flask_login import LoginManager, login_user, logout_user, current_user, login_required
from sqlalchemy.orm import sessionmaker, scoped_session
from sqlalchemy import Column, Integer, String, create_engine
from authlib.integrations.flask_oauth2 import AuthorizationServer
from authlib.oauth2.rfc6749 import grants
from authlib.oauth2.rfc7636 import CodeChallenge

import requests
from werkzeug.utils import secure_filename

from dynostore.controllers.auth import AuthController
from dynostore.decorators.token import validateToken, validateAdminToken
from dynostore.controllers.catalogs import CatalogController
from dynostore.controllers.data import DataController
from dynostore.controllers.datacontainer import DataContainerController
from dynostore.db import Session, Base, engine
from dynostore.models.user import User
from dynostore.models.oauth2 import OAuth2Client, OAuth2AuthorizationCode, OAuth2Token


app = Flask(__name__)
app.config["DEBUG"] = True
app.secret_key = 'supersecret'
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///db.sqlite'
db = Session()

Base.metadata.create_all(engine)

# === Flask-Login setup ===
login_manager = LoginManager()
login_manager.init_app(app)
login_manager.login_view = "login"


AUTH_HOST = os.getenv('AUTH_HOST')
PUB_SUB_HOST = os.getenv('PUB_SUB_HOST')
METADATA_HOST = os.getenv('METADATA_HOST')

@login_manager.user_loader
def load_user(user_id):
    return db.query(User).get(int(user_id))

def save_token(token_data, request):
    token = OAuth2Token(
        client_id=request.client.client_id,
        user_id=request.user.id,
        **token_data
    )
    db.add(token)
    db.commit()

# === Authlib OAuth2 setup ===
def query_client(client_id):
    return db.query(OAuth2Client).filter_by(client_id=client_id).first()

def save_token(token_data, request):
    token = OAuth2Token(
        client_id=request.client.client_id,
        user_id=request.user.id,
        **token_data
    )
    db.add(token)
    db.commit()

authorization = AuthorizationServer(
    app,
    query_client=query_client,
    save_token=save_token,
)

class AuthorizationCodeGrant(grants.AuthorizationCodeGrant):
    def save_authorization_code(self, code, request):
        auth_code = OAuth2AuthorizationCode(
            code=code,
            client_id=request.client.client_id,
            redirect_uri=request.redirect_uri,
            scope=request.scope,
            user_id=request.user.id,
            code_challenge=request.data.get("code_challenge"),
            code_challenge_method=request.data.get("code_challenge_method"),
        )
        db.add(auth_code)
        db.commit()
        return code

    def query_authorization_code(self, code, client):
        return db.query(OAuth2AuthorizationCode).filter_by(code=code, client_id=client.client_id).first()

    def delete_authorization_code(self, code):
        db.delete(code)
        db.commit()

    def authenticate_user(self, code):
        return db.query(User).get(code.user_id)

authorization.register_grant(AuthorizationCodeGrant, [CodeChallenge(required=True)])

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
@app.route("/login", methods=["GET", "POST"])
def login():
    if request.method == "GET":
        return render_template("login.html")

    payload = {
        "user": request.form["username"],
        "password": request.form["password"]
    }
    resp = requests.post(f"http://{AUTH_HOST}/auth/v1/users/login/", json=payload)

    print(resp.text, flush=True)

    if resp.status_code == 200:
        user_data = resp.json()
        user = db.query(User).filter_by(username=user_data["username"]).first()
        if not user:
            user = User(username=user_data["username"])
            db.add(user)
            db.commit()

        login_user(user)
        return redirect(request.args.get("next") or "/")

    return "Invalid credentials", 401

@app.route("/logout")
def logout():
    logout_user()
    return redirect("/")

@app.route("/oauth/authorize", methods=["GET", "POST"])
@login_required
def authorize():
    if request.method == "GET":
        try:
            grant = authorization.validate_consent_request(end_user=current_user)
        except Exception as e:
            return str(e), 400
        return render_template_string("""
            <form method="post">
                <p>Authorize {{client.client_id}}?</p>
                <button name="confirm" value="yes">Yes</button>
                <button name="confirm" value="no">No</button>
            </form>
        """, client=grant.client)

    if request.form.get("confirm") == "yes":
        return authorization.create_authorization_response(grant_user=current_user)
    return authorization.create_authorization_response(grant_user=None)

@app.route("/oauth/token", methods=["POST"])
def issue_token():
    return authorization.create_token_response()

@app.route("/")
def index():
    return f"Hello, {current_user.username}" if current_user.is_authenticated else "Welcome, please /login"

# === Seed user and client ===
def create_default_user_and_client():
    if not db.query(User).filter_by(username="alice").first():
        db.add(User(username="alice"))
    if not db.query(OAuth2Client).filter_by(client_id="my-cli").first():
        client = OAuth2Client(
            client_id="my-cli",
            client_secret="dummy-secret",
            redirect_uris="http://localhost:8080/callback",
            grant_types="authorization_code",
            response_types="code",
            scope="profile",
            token_endpoint_auth_method="none",
        )
        db.add(client)
    db.commit()



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
Route to download an object
"""
@app.route('/storage/<tokenuser>/<keyobject>', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
def downloadObject(tokenuser, keyobject):
    return DataController.pullData(request, tokenuser, keyobject, METADATA_HOST, PUB_SUB_HOST)

# DREX Experiments (temporal functions)
@app.route('/storage/<tokenuser>/<catalog>/<keyobject>', methods=["PUT"])
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
    create_default_user_and_client()
    app.run(debug=True, host='0.0.0.0', port=80)
