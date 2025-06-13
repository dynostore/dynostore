import requests
import os
import json

AUTH_URL = "http://localhost:8095"
TOKEN_FILE = os.path.expanduser("~/.mycli/token.json")

def request_user_code():
    resp = requests.post(f"{AUTH_URL}/device/code")
    resp.raise_for_status()
    data = resp.json()
    print(f"\nPlease go to {data['verification_uri']}")
    print(f"And enter this code to verify the client: {data['user_code']}\n")
    return data['user_code']

def input_user_token():
    return input("Paste the token shown in your browser here: ").strip()

def validate_token(user_token):
    resp = requests.post(f"{AUTH_URL}/token/validate", json={"token": user_token})
    print(resp)
    if resp.status_code == 200:
        token_data = resp.json()
        print("✅ Access token received!")
        return token_data
    else:
        print("❌ Invalid token:", resp.json())
        return None

def save_token(token_data):
    os.makedirs(os.path.dirname(TOKEN_FILE), exist_ok=True)
    with open(TOKEN_FILE, "w") as f:
        json.dump(token_data, f)
    print(f"🔐 Token saved at {TOKEN_FILE}")

def main():
    # Check if token already exists
    if os.path.exists(TOKEN_FILE):
        print(f"Token already exists at {TOKEN_FILE}. Skipping authentication.")
        return
    
    print("Starting authentication process...")
    user_code = request_user_code()
    print("Waiting for you to complete the verification in the browser...")

    user_token = input_user_token()
    token_data = validate_token(user_token)
    if token_data:
        save_token(token_data)

if __name__ == "__main__":
    main()
