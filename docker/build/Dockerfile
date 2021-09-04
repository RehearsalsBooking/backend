FROM ghcr.io/rehearsalsbooking/backend/php-base:latest

WORKDIR /app

COPY composer.lock composer.lock
COPY composer.json composer.json
COPY .env.example .env

RUN composer install --no-ansi --no-interaction --no-plugins --no-progress --optimize-autoloader --no-scripts --no-autoloader && \
    rm -rf /root/.composer

COPY . /app

RUN composer dump-autoload --optimize

EXPOSE 8000
CMD ["php","artisan","serve"]