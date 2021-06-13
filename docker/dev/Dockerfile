FROM docker.pkg.github.com/rehearsalsbooking/backend/php-base:latest

# Xdebug
RUN pecl install xdebug \
    &&  docker-php-ext-enable xdebug

# composer require checker
RUN composer global require maglnet/composer-require-checker \
    && ln -s /.composer/vendor/bin/composer-require-checker /usr/local/bin/composer-require-checker

# composer unused checker
RUN curl -L https://github.com/icanhazstring/composer-unused/releases/latest/download/composer-unused.phar -o composer-unused
RUN chmod a+x composer-unused
RUN mv composer-unused /usr/local/bin/composer-unused

WORKDIR /app