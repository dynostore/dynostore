import requests
import os

from dynoadmin import CREATEADMIN_ERROR, CREATEORG_ERROR, SUCCESS

#Read Gateway URL from environment variable
APIGATEWAY_URL = os.environ.get('APIGATEWAY_HOST')

def create_organization(acronym, fullname):
    """Create organization"""
    url = f"http://{APIGATEWAY_URL}/api/auth/organization"
    data = {
        "acronym": acronym,
        "fullname": fullname,
        "fathers_token": "/"
    }
    
    response = requests.post(url, json=data)

    if response.status_code != 200:
        raise Exception("Error creating organization")
    
    return response.json()['data']['tokenhierarchy']

def get_organization(acronym, name):
    url = f"http://{APIGATEWAY_URL}/api/auth/organization/{acronym}/{name}"
    response = requests.get(url)
    if response.status_code == 400:
        return False
    elif response.status_code == 200:
        return response.json()['data']['data']['tokenhierarchy']
    

def create_user(username, password, email):
    """Create  user"""
    #chek if organization exists
    organization = get_organization("default", "default")
    if not organization:
        organization = create_organization("default", "default")

    url = f"http://{APIGATEWAY_URL}/api/auth/user"
    data = {
        "username": username,
        "password": password,
        "email": email,
        "tokenorg": organization
    }
    
    response = requests.post(url, json=data)
    
    print(response.json())

    if response.status_code != 200:
        raise Exception("Error creating  user: " + response.json()['data']['message'])
    
    return response.json()
    
#create_user("user5455", "Auja7f8y.24", "default555@default.com")