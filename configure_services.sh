#/bin/bash

docker compose exec metadata /bin/sh -c "composer install && php artisan migrate && php artisan key:generate"
docker compose exec apigateway /bin/sh -c "composer install && php artisan key:generate"
