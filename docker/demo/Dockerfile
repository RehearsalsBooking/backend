FROM ghcr.io/rehearsalsbooking/backend/php-base:latest

RUN docker-php-ext-install opcache

COPY docker/production/php.ini-production "$PHP_INI_DIR/php.ini"

WORKDIR /app-demo

COPY composer.lock composer.lock
COPY composer.json composer.json
COPY .env.example .env

RUN composer install --no-ansi --no-interaction --no-plugins --no-progress --optimize-autoloader --no-scripts --no-autoloader && \
    rm -rf /root/.composer

COPY . /app-demo

RUN composer dump-autoload --optimize

RUN usermod -u 1000 www-data

EXPOSE 9000