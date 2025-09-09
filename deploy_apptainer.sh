echo "DynoStore - Apptainer Deployment Script"
echo "Welcome to the Apptainer deployment script!"

mkdir -p sif_images
cd sif_images

echo "Pulling DynoStore SIF images from Docker Hub..."
# check if images already exist
if [ -f "dynostore_metadata_v2.sif" ] && [ -f "dynostore_apigateway_python.sif" ] && 
    [ -f "nginx_1.17-alpine.sif" ] && [ -f "mysql_5.7.sif" ] && [ -f "dynostore_auth_v1.sif" ] && 
    [ -f "dynostore_databaseauth_v1.sif" ] && [ -f "dynostore_frontend_v1.sif" ] && 
    [ -f "dynostore_dbpubsub_v1.sif" ] && [ -f "dynostore_pubsub_v1.sif" ] && 
    [ -f "dynostore_datacontainer_v1.sif" ]; then
    echo "All images already exist. Skipping pull."
else
    apptainer pull dynostore_metadata_v2.sif docker://dynostore/metadata:v2
    apptainer pull dynostore_apigateway_python.sif docker://dynostore/apigateway:python
    apptainer pull nginx_1.17-alpine.sif docker://nginx:1.17-alpine
    apptainer pull mysql_5.7.sif docker://mysql:5.7
    apptainer pull dynostore_auth_v1.sif docker://dynostore/auth:v1
    apptainer pull dynostore_databaseauth_v1.sif docker://dynostore/databaseauth:v1
    apptainer pull dynostore_frontend_v1.sif docker://dynostore/frontend:v1
    apptainer pull dynostore_dbpubsub_v1.sif docker://dynostore/dbpubsub:v1
    apptainer pull dynostore_pubsub_v1.sif docker://dynostore/pubsub:v1
    apptainer pull dynostore_datacontainer_v1.sif docker://dynostore/datacontainer:v1
    echo "All images have been successfully pulled!"
fi


# Deploy the application using Apptainer
cd ..

########## API GATEWAY ##########

mkdir -p APIGateway/data
apptainer instance start -B "$(pwd)/APIGateway/data":/data -B "$(pwd)/APIGateway/app":/app sif_images/dynostore_apigateway_python.sif apigateway
apptainer exec --pwd /app \
  --env SQLALCHEMY_DATABASE_URI=sqlite:////data/app.db \
  instance://apigateway \
  hypercorn main:app --bind 0.0.0.0:8000 > hypercorn.log 2>&1 &


########## METADATA ############
apptainer instance start -B "$(pwd)/metadata/app":/var/www dynostore_metadata_v2.sif metadata