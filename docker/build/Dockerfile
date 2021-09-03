FROM ghcr.io/rehearsalsbooking/backend/php-base:latest

WORKDIR /app
COPY . /app

# TODO: remove dev dependencies once they are not needed
RUN composer install --no-ansi --no-interaction --no-plugins --no-progress --no-suggest --optimize-autoloader

RUN cp .env.example .env

EXPOSE 8000
CMD ["php","artisan","serve"]