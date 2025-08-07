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
docker compose -f docker-compose-dev.yml build
docker compose -f docker-compose-dev.yml up -d
```

> ℹ️ This command will start the metadata service, API gateway, and any other services defined in the `docker-compose-dev.yml` file.

### 2. Configure the Metadata Database

Initialize the database schema and apply initial configurations:

```bash
bash configure_services.sh
```

This script sets up the PostgreSQL schema and ensures required tables and migrations are in place.

### 3. Create Superuser

Create a superuser to access the admin API and manage the system:

```bash
bash generate_admin.sh
```

After execution, this script will print the admin token. **Save it**, as it is required to perform privileged operations like registering storage nodes.

### 4. Register a Local Data Container

Use the admin token from the previous step to register a local storage backend:

```bash
bash create_nodes.sh <Admin Token>
```

Replace `<Admin Token>` with the actual token you received.


This should display the interactive API documentation (Swagger UI).

## 🧹 Tear Down

To stop and remove all services:

```bash
docker compose -f docker-compose-dev.yml down
```