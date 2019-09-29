#FROM node:alpine as builder
#COPY . /app
#WORKDIR /app
#RUN npm install
#RUN npm run production

FROM evilfreelancer/dockavel:latest
COPY . /app
#COPY --from=builder /app/public/ /app/public
WORKDIR /app
RUN apk --update --no-cache add php7-mcrypt

RUN chown apache:1000 -R bootstrap \
 && chown apache:1000 -R storage \
 && composer install \
 && php artisan cache:clear

ENTRYPOINT ["/app/entrypoint.sh"]
