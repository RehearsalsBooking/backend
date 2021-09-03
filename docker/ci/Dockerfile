FROM ghcr.io/rehearsalsbooking/backend/php-base:latest

# composer require checker
RUN composer global require maglnet/composer-require-checker \
    && ln -s /.composer/vendor/bin/composer-require-checker /usr/local/bin/composer-require-checker

# composer unused checker
RUN curl -L https://github.com/icanhazstring/composer-unused/releases/latest/download/composer-unused.phar -o composer-unused
RUN chmod a+x composer-unused
RUN mv composer-unused /usr/local/bin/composer-unused

WORKDIR /app

COPY ./ /app

RUN composer install --no-ansi --no-interaction --no-plugins --no-progress --optimize-autoloader

RUN cp .env.example .env
