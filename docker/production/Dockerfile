FROM ghcr.io/rehearsalsbooking/backend/php-base:latest

COPY docker/production/php.ini-production "$PHP_INI_DIR/php.ini"

WORKDIR /app

COPY composer.lock composer.lock
COPY composer.json composer.json
COPY .env.example .env

RUN composer install --no-ansi --no-interaction --no-plugins --no-progress --optimize-autoloader --no-scripts --no-autoloader && \
    rm -rf /root/.composer

COPY . /app

RUN composer dump-autoload --optimize

# Add UID '1000' to www-data
RUN apk add shadow && usermod -u 1000 www-data && groupmod -g 1000 www-data

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /app

# Change current user to www
USER www-data

EXPOSE 9000