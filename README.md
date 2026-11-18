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


## 📦 Prerequisites

- Docker and Docker Compose installed
- Bash shell
- (Optional) Python and `httpie` or `curl` for interacting with the API

## 🚀 Deployment Steps


This guide describes how to deploy DynoStore in a single-node environment using Docker Compose, set up the metadata database, create an admin user, and register a local data container.


### 1. Build and Start the Services

To build the DynoStore services and launch them in the background:

```bash
docker compose -f docker-compose.dev.yml build
docker compose -f docker-compose.dev.yml up -d
```

> ℹ️ This command will start the metadata service, API gateway, and any other services defined in the `docker-compose.dev.yml` file.

> ℹ️ The scripts below use `docker-compose.dev.yml`.  Set `DYNOSTORE_PROJECT`
> to the compose project name if you started the stack with `-p <name>`, and
> `DYNOSTORE_COMPOSE_FILE` to use another compose file.  KAGIO analytics and the
> replicator are disabled by default because they need an external KAGIO
> service; enable them with `ENABLE_KAGIO=true ENABLE_REPLICATOR=true
> KAGIO_API_BASE_URL=http://<kagio-host>:8080`.  On arm64 hosts, `mysql:5.7` has
> no native image: export `DOCKER_DEFAULT_PLATFORM=linux/amd64` before step 1.

### 2. Configure the Metadata Database

Initialize the database schema and apply initial configurations:

```bash
bash configure_services.sh
```

This script waits for the metadata database and creates any missing tables (the metadata service also does this when it starts).

### 3. Create Superuser

Create a superuser to access the admin API and manage the system:

```bash
bash generate_admin.sh
```

After execution, this script will print the admin token on standard output. **Save it** (for example `bash generate_admin.sh > admin.token`), as it is required to perform privileged operations like registering storage nodes.

### 4. Register a Local Data Container

Use the admin token from the previous step to register a local storage backend:

```bash
bash create_nodes.sh <Admin Token>
```

Replace `<Admin Token>` with the actual token you received.


## 🧰 Install the DynoStore Client

You can install the DynoStore client in an isolated Python environment using `virtualenv`:

```bash
python3 -m venv dynostore-env
source dynostore-env/bin/activate
pip install --upgrade pip
pip install git+https://github.com/dynostore/dynostore-client
```

After installation, you can use the `dynostore` command-line tool to interact with the deployed services.



## 🧹 Tear Down

To stop and remove all services:

```bash
docker compose -f docker-compose.dev.yml down
```


## 🧪 DynoStore Client Usage Examples

Once installed, you can use the `dynostore` CLI tool to interact with the deployed metadata server.

### Upload a File

```bash
dynostore --server 127.0.0.1:8000 put ./data.csv --catalog my-catalog
```

### Upload a File with Encryption and Resiliency Level

```bash
dynostore --server 127.0.0.1:8000 put ./data.csv --catalog my-catalog --encrypt --resiliency 1
```

### Upload All Files in a Directory

```bash
dynostore --server 127.0.0.1:8000 put ./myfolder --catalog my-catalog --recursive
```

### Download an Object by Key

```bash
dynostore --server 127.0.0.1:8000 get <key> --output downloaded.csv
```

### Download All Objects from a Catalog

```bash
dynostore --server 127.0.0.1:8000 get_catalog my-catalog output_dir/
```

### Check if an Object Exists

```bash
dynostore --server 127.0.0.1:8000 exists <key>
```

### Evict (Delete) an Object

```bash
dynostore --server 127.0.0.1:8000 evict <key>
```

> 📝 Replace `<key>` with the object key returned by the `put` command.