<p align="center" style="padding-top: 20px; padding-bottom: 20px;">
   <img src="static/logo-light.jpg#only-light" title="DynoStore Logo" width="35%">
   <img src="static/logo-dark.png#only-dark" title="DynoStore Logo" width="35%">
</p>


# DynoStore

DynoStore is a content delivery network that facilitates the connection of multiple storage infraestructures to create a data fabric. At the core of DynoStore entities called data containers orchestrate the storage and movement of data through multiple sites. These containers include generic interfaces to interconnect sites implementing different storage systems (e.g., Amazon S3, Ceph, or Lustre).

DynoStore components are divided on client and server components. Server components are microservices deployed to manage the metadata of contents and  user authentication. Whereas, client components include functions to push and pull data.

## Services installation using Docker (recommended)

DynoStore architechtures follows a microservice design, which includes the following components declared in the ```docker-compose.yml```: 

+ ```apigateway```: serves as interface for clients and applications pushing and pulling data using DynoStore. 
+ ```auth```: for user registration and authentication.
+ ```frontend```: GUI to navigate through uploaded data.
+ ```pub_sub```: to manage the sharing of data based on a publication/subscription service.
+ ```metadata```: to manage the metadata.
+ ```datacontainer1, datacontainer2, datacontainer3, datacontainer4, datacontainer5```: data containers to store data.

```bash
docker compose up -d
```

For a further description of how to deploy these backend services, please refer to [Backend installation](/installation)

## Client instalation and usage

ProxyStore can be accessed through a Python client. This client implement evict, exists, get, and put functions. 

```python
from dynostore.client import Client
import uuid

token_user = "<<token-user-0>>"
catalog = "catalog-0"
data = b"Hello, World!"


client = Client("<<API_GATEWAY_IP>>")

client.put(
    data = data,
    token_user = token_user,
    catalog = catalog
)

received_data = client.get(
    key = key,
    token_user = token_user
)
```

## DynoStore connector for ProxyStore

We implemented a DynoStore connector that can be used with [ProxyStore](https://docs.proxystore.dev/) for the transparent management of Python objects as proxies. Getting starte with DynoStore connector requires a few lines of Python code:

```python
from proxystore.proxy import Proxy
from proxystore.store import Store
from proxystore.connectors.cdn import DynoConnector

# Create a DynoStore connector 
connector = DynoConnector(apigateway="<<API_GATEWAY_IP>>", token_user="<<token-user-0>>", catalog="proxystore")
store = Store('my-store', connector)

data = 3

# Store the object and get a proxy
proxy = store.proxy(data)

# Work on the proxy which behavies like the original object
data = proxy ** 2
```

Check out [ProxyStore documentation](https://docs.proxystore.dev/) to learn more!