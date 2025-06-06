import os
import webbrowser
import http.server
import threading
import requests
import base64
import hashlib
import uuid

AUTH_URL = "http://localhost:8095/oauth/authorize"
TOKEN_URL = "http://localhost:8095/oauth/token"
CLIENT_ID = "my-cli"
REDIRECT_URI = "http://localhost:20090/callback"

def generate_pkce():
    code_verifier = base64.urlsafe_b64encode(os.urandom(40)).rstrip(b'=').decode('utf-8')
    code_challenge = base64.urlsafe_b64encode(
        hashlib.sha256(code_verifier.encode()).digest()
    ).rstrip(b'=').decode('utf-8')
    return code_verifier, code_challenge

class OAuthCallbackHandler(http.server.BaseHTTPRequestHandler):
    def do_GET(self):
        global authorization_code
        if '/callback' in self.path:
            from urllib.parse import urlparse, parse_qs
            query = parse_qs(urlparse(self.path).query)
            authorization_code = query.get('code', [None])[0]
            self.send_response(200)
            self.end_headers()
            self.wfile.write(b"Authentication successful. You may close this window.")
        else:
            self.send_error(404)

def start_local_server():
    server = http.server.HTTPServer(('localhost', 20090), OAuthCallbackHandler)
    threading.Thread(target=server.serve_forever, daemon=True).start()

def authenticate():
    global authorization_code
    authorization_code = None
    code_verifier, code_challenge = generate_pkce()

    # Start local server
    start_local_server()

    # Open browser for login
    auth_url = f"{AUTH_URL}?response_type=code&client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&code_challenge={code_challenge}&code_challenge_method=S256"
    print("Opening browser for authentication...")
    webbrowser.open(auth_url)

    # Wait until code is received
    import time
    while authorization_code is None:
        time.sleep(1)

    # Exchange code for tokens
    data = {
        "grant_type": "authorization_code",
        "code": authorization_code,
        "redirect_uri": REDIRECT_URI,
        "client_id": CLIENT_ID,
        "code_verifier": code_verifier,
    }
    response = requests.post(TOKEN_URL, data=data)
    tokens = response.json()

    # Store tokens securely
    os.makedirs(os.path.expanduser("~/.mycli/"), exist_ok=True)
    with open(os.path.expanduser("~/.mycli/tokens.json"), "w") as f:
        import json
        json.dump(tokens, f)

    print("Access token saved.")
    return tokens

if __name__ == "__main__":
    authenticate()
    print("Authentication complete. You can now use the CLI.")