FROM docker.pkg.github.com/rehearsalsbooking/backend/php-base:latest

# Xdebug
RUN pecl install xdebug \
    &&  docker-php-ext-enable xdebug

WORKDIR /app
COPY . /app

RUN composer install --no-ansi --no-interaction --no-plugins --no-progress --no-suggest --optimize-autoloader