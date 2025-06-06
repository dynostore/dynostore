from client import Client

data = "abc"
data_bytes = data.encode("utf-8")

client = Client("localhost:5000")

response = client.put(data_bytes, "", "", is_encrypted=True)