import requests

url = "http://localhost:80/metadata/493133c651bd5ea94532c84b6be48a3e49d734cf53587f84e23235227cb389f9/94780189-1b0c-4ca0-9e1f-73c4bfdc9367"
payload = {
    "name": "test",
    "size": 100,
    "hash": "xxx",
    "key": "94780189-1b0c-4ca0-9e1f-73c4bfdc9367",
    "is_encrypted": 0,
    "resiliency": 1,
    "nodes": []
}
try:
    resp = requests.post(url, json=payload)
    print("Status:", resp.status_code)
    print("Body:", resp.text)
except Exception as e:
    print("Error:", e)
