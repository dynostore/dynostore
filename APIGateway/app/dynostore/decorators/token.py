from functools import wraps
from quart import jsonify, request
from dynostore.controllers.auth import AuthController
import asyncio

def validateToken(auth_host: str = 'auth'):
    def decorator(f):
        @wraps(f)
        async def wrapper(*args, **kwargs):
            tokenUser = kwargs.get('tokenuser')
            response, code = await asyncio.to_thread(AuthController.validateUserToken, tokenUser, auth_host)
            if code != 200:
                return jsonify({'message': 'Unauthorized'}), 401
            return await f(*args, **kwargs)
        return wrapper
    return decorator

def validateAdminToken(auth_host: str = 'auth'):
    def decorator(f):
        @wraps(f)
        async def wrapper(*args, **kwargs):
            admintoken = kwargs.get('admintoken')
            response, code = await asyncio.to_thread(AuthController.validateAdminToken, admintoken, auth_host)
            if code != 200:
                return jsonify({'message': 'Unauthorized'}), 401
            return await f(*args, **kwargs)
        return wrapper
    return decorator
