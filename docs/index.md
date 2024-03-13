<p align="center" style="padding-top: 20px; padding-bottom: 20px;">
   <img src="static/logo-light.jpg#only-light" title="DynoStore Logo" width="35%">
   <img src="static/logo-dark.png#only-dark" title="DynoStore Logo" width="35%">
</p>


# DynoStore

DynoStore is a content delivery network that facilitates the connection of multiple storage infraestructures to create a data fabric. At the core of DynoStore entities called data containers orchestrate the storage and movement of data through multiple sites. These containers include generic interfaces to interconnect sites implementing different storage systems (e.g., Amazon S3, Ceph, or Lustre).

DynoStore components are divided on client and server components. Server components are microservices deployed to manage the metadata of contents and  user authentication. Whereas, client components include functions to push and pull data.

## Services installation using Docker (recommended)

Los siguientes servicios se encuentran declarados en el archivo ```docker-compose.yml```. 

+ ```apigateway```: API Gateway 
+ ```auth```: User authentication.
+ ```frontend```: GUI to navigate through uploaded files.
+ ```pub_sub```: Publication/subscription service.
+ ```metadata```: Metadata service.
+ ```storage1, storage2, storage3, storage4, storage5```: storage containers.

```bash
docker compose up -d
```

## Client instalation and usage

ProxyStore can be accessed through a Python client. This client implement evict, exists, get, and put functions. 

```python
from dynostore.client import Client
import uuid

token_user = "user-0"
catalog = "catalog-0"
data = b"Hello, World!"


client = Client("metadata_server")

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