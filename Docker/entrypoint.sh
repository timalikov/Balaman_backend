#!/bin/bash

if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing dependencies"
    composer install --no-progress --no-interaction
fi


if [ ! -f ".env" ]; then
    echo "Creating .env file for env $APP_ENV"
    cp .env.example .env
else 
    echo "Using existing .env file"
fi

php artisan migrate
php artisan key generate
php artisan cache:clear
php artisan config:clear
php artisan route:clear

php artisan serve --port=$PORT --host=0.0.0.0 --env=.env
exec docker-php-entrypoint "$@"
