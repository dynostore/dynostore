import os
import time
import asyncio
import requests
from uuid import uuid4

from quart import Quart, jsonify, request, render_template, redirect, url_for, session
from quart_auth import AuthUser, login_required, login_user, logout_user, current_user
from quart_sqlalchemy import SQLAlchemyConfig
from quart_sqlalchemy.framework import QuartSQLAlchemy
from sqlalchemy.orm import Mapped, mapped_column


from dynostore.controllers.auth import AuthController
from dynostore.controllers.catalogs import CatalogController
from dynostore.controllers.data import DataController
from dynostore.controllers.datacontainer import DataContainerController
from dynostore.decorators.token import validateToken, validateAdminToken

from dynostore.db import db

app = Quart(__name__)
app.secret_key = 'supersecret'
app.config["DEBUG"] = True
app.config['MAX_CONTENT_LENGTH'] = 1024 * 1024 * 1024

#app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///db.sqlite'

#from hypercorn.config import Config
#print("Starting with body limit:", Config().body_limit, flush=True)

db = QuartSQLAlchemy(
  config=SQLAlchemyConfig(
      binds=dict(
          default=dict(
              engine=dict(
                  url="sqlite:///db.sqlite",
                  echo=True,
                  connect_args=dict(check_same_thread=False),
              ),
              session=dict(
                  expire_on_commit=False,
              ),
          )
      )
  ),
  app=app,
)


class DeviceCode(db.Model):
    id: Mapped[int] = mapped_column(primary_key=True)
    device_code: Mapped[str] = mapped_column(unique=True, nullable=False)
    user_code: Mapped[str] = mapped_column(unique=True, nullable=False)
    expires_at: Mapped[int] = mapped_column(nullable=False)
    interval: Mapped[int] = mapped_column(default=5)
    verified: Mapped[bool] = mapped_column(default=False)
    username: Mapped[str] = mapped_column(nullable=True)

class AccessToken(db.Model):
    id: Mapped[int] = mapped_column(primary_key=True)
    token: Mapped[str] = mapped_column(unique=True, nullable=False)
    username: Mapped[str] = mapped_column(nullable=False)
    expires_at: Mapped[int] = mapped_column(nullable=False)

    def to_dict(self):
        return {
            "id": self.id,
            "token": self.token,
            "username": self.username,
            "expires_at": self.expires_at
        }

class User(db.Model):
    __tablename__ = 'users'
    id: Mapped[int] = mapped_column(primary_key=True)
    username: Mapped[str] = mapped_column(unique=True)
    access_token: Mapped[str] = mapped_column(nullable=False)
    user_token: Mapped[str] = mapped_column(nullable=False)
    api_key: Mapped[str] = mapped_column(nullable=False)

    def get_id(self):
        return str(self.id)

    @property
    def is_authenticated(self):
        return True  # Flask-Login needs this
    
    def to_dict(self):
        return {
            "username": self.username,
            "access_token": self.access_token,
            "user_token": self.user_token,
            "api_key": self.api_key
        }

AUTH_HOST = os.getenv('AUTH_HOST')
PUB_SUB_HOST = os.getenv('PUB_SUB_HOST')
METADATA_HOST = os.getenv('METADATA_HOST')
PUBLIC_IP = os.getenv('PUBLIC_IP', 'localhost')

db.create_all()


# @app.before_serving
# async def startup():
#     with app.app_context():
#         db.create_all()

@app.route("/device", methods=["GET", "POST"])
async def device_entry():
    if request.method == "GET":
        return await render_template("device_verification.html")

    form = await request.form
    user_code = form.get("user_code")

    with app.app_context():
        device = DeviceCode.query.filter_by(user_code=user_code).first()

    if not device:
        return "Invalid code", 400

    session["device_code"] = device.device_code
    return await render_template("login.html", device_code=device.device_code)

@app.route("/token/validate", methods=["POST"])
async def verify_token():
    data = await request.get_json() or await request.form
    token = data.get("token")

    if not token:
        return jsonify({"error": "missing_token"}), 400

    with app.app_context():
        token_entry = AccessToken.query.filter_by(token=token).first()
        now = int(time.time())
        if token_entry and token_entry.expires_at > now:
            user = User.query.filter_by(username=token_entry.username).first()
            if not user:
                return jsonify({"error": "user_not_found"}), 404
            return jsonify(user.to_dict()), 200

    return jsonify({"error": "invalid_or_expired_token"}), 401

@app.route("/me")
async def profile():
    auth = request.headers.get("Authorization")
    if not auth or not auth.startswith("Bearer "):
        return jsonify({"error": "missing_token"}), 401

    token = auth.split()[1]
    with app.app_context():
        token_entry = AccessToken.query.filter_by(token=token).first()
        now = int(time.time())
        if not token_entry or token_entry.expires_at <= now:
            return jsonify({"error": "invalid_or_expired_token"}), 401

    return jsonify({"username": token_entry.username, "token": token})

@app.route('/auth/organization/<name>/<acronym>', methods=["PUT"])
async def createOrganization(name, acronym):
    data = await request.get_json()
    url_service = f'http://{AUTH_HOST}/auth/v1/hierarchy'
    payload = {"option": "NEW", "fullname": name, "acronym": acronym, "fathers_token": data['fathers_token']}
    results = requests.post(url_service, json=payload)
    return jsonify(results.json()), results.status_code

@app.route('/auth/organization/<name>/<acronym>', methods=["GET"])
async def getOrganization(name, acronym):
    url_service = f'http://{AUTH_HOST}/auth/v1/hierarchy'
    data = {"option": "CHECK", "fullname": name, "acronym": acronym}
    results = requests.post(url_service, json=data)
    return jsonify(results.json()), results.status_code

@app.route('/auth/organization', methods=["GET"])
async def getOrganizations():
    url_service = f'http://{AUTH_HOST}/auth/v1/hierarchy/all/'
    results = requests.get(url_service)
    return jsonify(results.json()), results.status_code

@app.route('/auth/user', methods=["POST"])
async def createUser():
    data = await request.get_json()
    url_service = f'http://{AUTH_HOST}/auth/v1/users/create'
    payload = {
        "option": "NEW",
        "email": data['email'],
        "password": data['password'],
        "username": data['username'],
        "tokenorg": data['tokenorg']
    }
    results = requests.post(url_service, json=payload)
    return jsonify(results.json()), results.status_code

@app.route('/auth/user/<tokenuser>', methods=["GET"])
async def validateUsertToken(tokenuser):
    return AuthController.validateUsertToken(tokenuser, AUTH_HOST)

@app.route("/auth/user/login", methods=["GET", "POST"])
async def login():
    if request.method == "GET":
        return await render_template("login.html")

    inputs = await request.get_json() or await request.form

    if "username" not in inputs or "password" not in inputs:
        return "Missing username or password", 400

    payload = {"user": inputs["username"], "password": inputs["password"]}
    resp = requests.post(f"http://{AUTH_HOST}/auth/v1/users/login/", json=payload)

    if resp.status_code == 200:
        user_data = resp.json()["data"]

        if request.args.get("type") == "device":
            form = await request.form
            device_code = form.get("device_code")
            with app.app_context():
                device = DeviceCode.query.filter_by(device_code=device_code).first()
            if not device:
                return "Invalid session", 400

            token = str(uuid4())
            expires_at = int(time.time()) + 3600

            device.verified = True
            device.username = inputs["username"]
            with app.app_context():
                db.session.add(AccessToken(token=token, username=inputs["username"], expires_at=expires_at))
                db.session.commit()

            with app.app_context():
                user = User.query.filter_by(username=user_data["username"]).first()
                if not user:
                    user = User(username=user_data["username"], access_token=user_data["access_token"],
                                user_token=user_data["tokenuser"], api_key=user_data["apikey"])
                    db.session.add(user)
                elif user.user_token != user_data["tokenuser"]:
                    user.access_token = user_data["access_token"]
                    user.user_token = user_data["tokenuser"]
                    user.api_key = user_data["apikey"]
                db.session.commit()

            return await render_template("auth_success.html", token=token)

        return resp.json(), 200

    return "Invalid credentials", 401

@app.route("/logout")
async def logout():
    logout_user()
    return redirect("/")

@app.route('/pubsub/<tokenuser>/catalog/<catalogname>', methods=["PUT"])
@validateToken(auth_host=AUTH_HOST)
async def createCatalog(tokenuser, catalogname):
    data = await request.get_json()
    return CatalogController.createOrGetCatalog(
        request, PUB_SUB_HOST, catalogname, tokenuser,
        data.get('dispersemode', "SINGLE"),
        data.get('encryption', 0),
        data.get('fathers_token', "/"),
        data.get('processed', 0)
    )

@app.route('/pubsub/<tokenuser>/catalog/<catalog>', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
async def getCatalog(tokenuser, catalog):
    return CatalogController.getCatalog(PUB_SUB_HOST, catalog, tokenuser)

@app.route('/pubsub/<tokenuser>/catalog/<catalog>', methods=["DELETE"])
@validateToken(auth_host=AUTH_HOST)
async def deleteCatalog(tokenuser, catalog):
    url_service = f'http://{PUB_SUB_HOST}/catalog/{catalog}/?tokenuser={tokenuser}'
    results = requests.delete(url_service)
    return jsonify(results.json()), results.status_code

@app.route('/pubsub/<tokenuser>/catalog/<catalog>/list', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
async def listCatalogFiles(tokenuser, catalog):
    return CatalogController.listFilesInCatalog(PUB_SUB_HOST, catalog, tokenuser)

@app.route('/storage/<tokenuser>/<keyobject>', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
async def downloadObject(tokenuser, keyobject):
    return await DataController.pull_data(tokenuser, keyobject, METADATA_HOST, PUB_SUB_HOST)

@app.route('/storage/<tokenuser>/<catalog>/<keyobject>', methods=["PUT"])
@validateToken(auth_host=AUTH_HOST)
async def uploadObjectDREX(tokenuser, catalog, keyobject):
    return await DataController.push_data(request, METADATA_HOST, PUB_SUB_HOST, catalog, tokenuser, keyobject)

@app.route('/metadata/<tokenuser>/<keyobject>', methods=["POST"])
@validateToken(auth_host=AUTH_HOST)
async def upload_metadata(tokenuser, keyobject):
    return await DataController.upload_metadata(request, tokenuser, keyobject)

@app.route('/upload/<tokenuser>/<catalog>/<keyobject>', methods=["PUT"])
@validateToken(auth_host=AUTH_HOST)
async def upload_data(tokenuser, catalog, keyobject):
    return await DataController.upload_data(request, METADATA_HOST, PUB_SUB_HOST, catalog, tokenuser, keyobject)

@app.route('/storage/<tokenuser>/<keyobject>/exists', methods=["GET"])
@validateToken(auth_host=AUTH_HOST)
async def existsObject(tokenuser, keyobject):
    return DataController.exists_object(request, tokenuser, keyobject, METADATA_HOST)

@app.route('/storage/<tokenuser>/<keyobject>', methods=["DELETE"])
@validateToken(auth_host=AUTH_HOST)
async def deleteObject(tokenuser, keyobject):
    return DataController.delete_object(request, tokenuser, keyobject, METADATA_HOST)

@app.route('/datacontainer/<admintoken>', methods=["POST"])
@validateAdminToken(auth_host=AUTH_HOST)
async def registDataContainer(admintoken):
    return await DataContainerController.regist(request, admintoken, METADATA_HOST)

@app.route('/datacontainer/<admintoken>/all', methods=["DELETE"])
@validateAdminToken(auth_host=AUTH_HOST)
async def deleteAllDCs(admintoken):
    return await DataContainerController.delete_all(request, admintoken, METADATA_HOST)

@app.route("/statistic/<admintoken>", methods=["GET"])
@validateAdminToken(auth_host=AUTH_HOST)
async def statistics(admintoken):
    url_service = f"http://{METADATA_HOST}/api/servers/{admintoken}"
    results = requests.get(url_service)
    return jsonify(results.json()), results.status_code

@app.route("/clean/<admintoken>", methods=["GET"])
@validateAdminToken(auth_host=AUTH_HOST)
async def clean(admintoken):
    url_service = f'http://{METADATA_HOST}/api/clean'
    results = requests.get(url_service)
    return jsonify(results.json()), results.status_code


if __name__ == "__main__":
    import hypercorn.asyncio
    # from hypercorn.config import Config

    # config = Config()
    # config.bind = ["0.0.0.0:80"]
    # config.worker_class = "asyncio"
    print("Starting with body limit:", Config().body_limit, flush=True)
    asyncio.run(hypercorn.asyncio.serve(app, config))
    #asyncio.run(app.run_task())

    #from hypercorn.config import Config
    #print("Starting with body limit:", Config().body_limit, flush=True)
    #app.run(host='0.0.0.0', port=80)
