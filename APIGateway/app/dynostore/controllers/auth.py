import requests


class AuthController():

    @staticmethod
    def validateUserToken(
        token: str,
        auth_host: str
    ):
        url = f'http://{auth_host}/auth/v1/user?tokenuser={token}'
        response = requests.get(url)
        return response.json(), response.status_code
