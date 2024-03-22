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

