FROM ghcr.io/rehearsalsbooking/backend/php-base:latest

WORKDIR /app

COPY composer.lock composer.lock

# TODO: remove dev dependencies once they are not needed
RUN composer install --no-ansi --no-interaction --no-plugins --no-progress --optimize-autoloader && rm -rf /root/.composer

RUN cp .env.example .env

COPY . /app

EXPOSE 8000
CMD ["php","artisan","serve"]