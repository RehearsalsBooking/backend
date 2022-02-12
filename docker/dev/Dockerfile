FROM ghcr.io/rehearsalsbooking/backend/php-base:latest

# Xdebug
RUN pecl install xdebug \
    &&  docker-php-ext-enable xdebug

# composer require checker
RUN composer global require maglnet/composer-require-checker \
    && ln -s /.composer/vendor/bin/composer-require-checker /usr/local/bin/composer-require-checker

WORKDIR /app