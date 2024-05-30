import requests
from functools import wraps
from flask import jsonify, make_response


from dynostore.controllers.auth import AuthController

"""
Token validation
"""
def validateToken(auth_host: str = 'auth'):
    def decorator(f):
        def wrapper(*args, **kwargs):
            tokenUser = kwargs.get('tokenuser')
            response, code = AuthController.validateUserToken(tokenUser, auth_host)
            if code != 200:
                return make_response(jsonify({'message': 'Unauthorized'}), 401)
            
            return f(*args, **kwargs)
        wrapper.__name__ = f.__name__
        return wrapper
    return decorator


"""
Admin token validation
"""
def validateAdminToken(auth_host: str = 'auth'):
    def decorator(f):
        def wrapper(*args, **kwargs):
            admintoken = kwargs.get('admintoken')
            response, code = AuthController.validateAdminToken(admintoken, auth_host)
            
            print(response, flush=True)
            if code != 200:
                return make_response(jsonify({'message': 'Unauthorized'}), 401)
            
            return f(*args, **kwargs)
        wrapper.__name__ = f.__name__
        return wrapper
    return decorator