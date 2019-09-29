#!/usr/bin/env bash

set -e

role=${CONTAINER_ROLE:-app}
env=${APP_ENV:-production}

cd /app

if [ "$env" != "local" ]; then
    echo "Caching configuration..."
    (php artisan cache:clear && php artisan route:clear && chown apache:apache -R /app/bootstrap/ /app/storage/)
fi

if [ "$role" = "app" ]; then

    # Check if .env file is not exist then create new one
    if [[ ! -e .env ]]; then
        cp .env.example .env
        chown 1000:1000 .env
        php artisan key:generate
    fi

    chown -R apache:1000 /app/vendor /app/composer.lock
    chown -R apache:1000 /app/storage

    php artisan migrate --force

    /entrypoint.sh

elif [ "$role" = "queue" ]; then

    echo "Queue role"
    php artisan queue:work

elif [ "$role" = "scheduler" ]; then

    while [ true ]
    do
      php artisan schedule:run --verbose --no-interaction &
      sleep 60
    done

else
    echo "Could not match the container role \"$role\""
    exit 1
fi
