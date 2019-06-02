FROM php:7.3-fpm-stretch

# Install modules
RUN apt-get clean && apt-get update && apt-get install -y \
        cron \
        supervisor \
        sendmail \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libicu-dev \
        libxslt1-dev \
        openssl \
        imagemagick \
        libmagickwand-dev \
        git \
        libmemcached-dev \
        libzip-dev \
        libpq-dev \
        gnupg \
            --no-install-recommends

RUN docker-php-ext-install intl pdo pdo_pgsql zip sockets soap xsl

RUN docker-php-ext-configure gd \
        --with-freetype-dir=/usr/lib/x86_64-linux-gnu/ \
        --with-png-dir=/usr/lib/x86_64-linux-gnu/ \
        --with-jpeg-dir=/usr/lib/x86_64-linux-gnu/ \
    && docker-php-ext-install gd

# Imagick
RUN pecl install imagick \
    && echo "extension=imagick.so" > /usr/local/etc/php/conf.d/imagick.ini

# Xdebug
RUN pecl install xdebug \
    &&  docker-php-ext-enable xdebug

# Config
RUN echo "zend_extension=opcache.so \nopcache.memory_consumption=128M \nopcache.interned_strings_buffer=8 \nopcache.max_accelerated_files=4000 \nopcache.max_wasted_percentage=5 \nopcache.validate_timestamps=1 \nopcache.revalidate_freq=60 \nopcache.enable_cli=1 \nopcache.enable_file_override=1 \nopcache.fast_shutdown=1 \nopcache.blacklist_filename=/etc/php.d/opcache*.blacklist" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "error_log = /var/log/php7-fpm/php7-fpm.log \nlog_errors = On \nerror_reporting = E_ERROR | E_PARSE \ndisplay_errors = On" >> /usr/local/etc/php/conf.d/log.ini \
    && sed -i 's/;catch_workers_output.*/catch_workers_output = yes/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;php_admin_value\[error_log\].*/php_admin_value\[error_log\] = \/var\/log\/php7-fpm\/php7-fpm.log/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;php_admin_flag\[log_errors\].*/php_admin_flag\[log_errors\] = on/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/pm.max_children = .*/pm.max_children = 200/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/pm.start_servers = .*/pm.start_servers = 10/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/pm.min_spare_servers = .*/pm.min_spare_servers = 10/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/pm.max_spare_servers = .*/pm.max_spare_servers = 100/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/pm = .*/pm = dynamic/g' /usr/local/etc/php-fpm.d/www.conf

# Logs
RUN mkdir -p /var/log/php7-fpm && \
    chown -R www-data:www-data /var/log/php7-fpm

# Install composer && global asset plugin
ENV COMPOSER_HOME /root/.composer
ENV PATH /root/.composer/vendor/bin:$PATH
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    /usr/local/bin/composer global require "fxp/composer-asset-plugin"

# Clear
RUN apt-get purge -y g++ \
    && apt-get autoremove -y \
    && rm -r /var/lib/apt/lists/* \
    && rm -rf /tmp/*

# Create supervisor config and crontab updater (Cron and php work together)
RUN mkdir -p /crontab/
ADD cron/crontab-updater.sh /crontab
RUN mkdir -p /var/log/supervisor

# Fake sendmail
RUN echo "sendmail_path = /app/docker/sendmail/fake_sendmail.sh" > /usr/local/etc/php/conf.d/fake_sendmail.ini

RUN usermod -u 1000 www-data

EXPOSE 9000
CMD ["/usr/bin/supervisord"]