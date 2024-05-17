# Backend installation 

We recommend install the DynoStore backend using Docker. Docker containers provide an agile deployment over multiple operating systems and platforms. To install Docker on your computer, please refer to [their official documentation](https://www.docker.com/).

## Prebuild Docker images 🐋

Prebuilt docker images are the easiest way to start using DynoStore locally. They are available on Docker Hub:

* [dynostore/apigateway:python](https://hub.docker.com/repository/docker/dynostore/apigateway/general)
* [dynostore/metadata:v2](https://hub.docker.com/repository/docker/dynostore/metadata/general)
* [dynostore/pubsub:v1](https://hub.docker.com/repository/docker/dynostore/pubsub/general)
* [dynostore/auth:v1](https://hub.docker.com/repository/docker/dynostore/auth/general)
* [dynostore/datacontainer:v1](https://hub.docker.com/repository/docker/dynostore/datacontainer/general)

Aditionally, there are available the database containers with the SQL scheme pre-loaded.

* [dynostore/databaseauth:v1](https://hub.docker.com/repository/docker/dynostore/databaseauth/general)
* [dynostore/dbpubsub:v1](https://hub.docker.com/repository/docker/dynostore/dbpubsub/general)

## Build Docker images

Alternatively, you can build your own images by executing the following command:

```bash
docker compose build
```

## Quick installation guide

* Clone DynoStore source code from the [GitHub repository](https://github.com/dynostore/dynostore).
```bash 
git clone https://github.com/dynostore/dynostore
cd dynostore
```

* Deploy Docker containers on your machine. It will take some time to download the latest DynoStore release and its related services from DockerHub and create containers.

```bash
docker compose up -d
```

* Complete the deployment of the ```APIGateway``` and ```metadata``` services by migrating their databases using the following command:

```bash
bash configure_services.sh
```

### Creating an admin account

You can register a user but by default, it will not have rights even to view and manage data containers. Thus, you should create an admin user. Please use the command below:

```bash
docker exec -it db_auth bash -ic 'python3 configure/create_admin.py'
```

You can modify the admin credentials by changing the environment variables of the ```db_auth``` container in the ```docker-compose.yml``` file:

```yaml
db_auth: #Authentication database
    image: dynostore/databaseauth:muyal
    build: 
      context: ./auth
      dockerfile: DB.Dockerfile
    expose:
      - "5432"
    environment:
      POSTGRES_DB: auth
      POSTGRES_USER: muyalmanager
      POSTGRES_PASSWORD: niCi7unamltrubrlJusp
      ADMIN_USER: dynoadmin # (1)
      ADMIN_PASSWORD: XeN5raSsdJkcOMN
      ADMIN_EMAIL: admin@admin
    volumes:
      - psql-auth:/var/lib/postgresql/data
      - ./auth/schema-sql/auth.sql:/docker-entrypoint-initdb.d/auth.sql
      - ./auth/configure:/configure
    restart: always
```

1. Admin credentials.

### Deploying a data container

Data containers are abstractions used in DynoStore to manage as code the available storage infraestructure. A data container includes interfaces to enable the ```get``` and ```put``` of data. In the quick installation of DynoStore, the file ```docker-compose.yml``` already includes five datacontainers ready to use. 

Create a new data container adding the following lines to the ```docker-compose.yml``` file:

```yaml
storage6:
    image: dynostore/datacontainer
    ports:
        - "20011:80" # (1)
    volumes: # (2)
        - ./storage/storage6/data:/data
        - ./storage/storage6/abekeys:/var/www/html/abekeys
    environment:  # (3)
        AUTH_HOST: auth
```

1. The port ```20011``` must be opened. This is port used by the container to receive requests.
2. Volumes between the host and the container to share data and keys.
3. Environment variables to left the container access to the authentication services.

Save the ```docker-compose.yml``` and deploy the changes with:

```bash
docker compose up -d
```

### Registering a data container

A data container must be registered  on DynoStore to let the metadata server know it is available. This is performed by executing the following command:

```bash
docker compose exec storage6 bash regist_container.sh tokenuser memory storage
```

The ```tokenuser``` parameter refers to a key assigned to the administrator of the system to add and remove data containers from DynoStore. To get your tokens, please refer to [Creating an admin account](#creating-an-admin-account).
