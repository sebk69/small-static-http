FROM php:8.1-cli

# args
ARG memory_limit=500M
ARG timezone=Europe/Paris
ARG user_id=1000

# install openswoole
RUN pecl install openswoole
RUN docker-php-ext-enable openswoole

# install zip php extension
RUN apt-get update && apt-get install -y zip libzip-dev
RUN docker-php-ext-install zip

# set memory limit
RUN echo 'memory_limit = 500M' >> /usr/local/etc/php/conf.d/docker-php-ram-limit.ini

# Set timezone
RUN cp /usr/share/zoneinfo/$timezone /etc/localtime \
    && echo "$timezone" > /etc/timezone \
    && echo "[Date]\ndate.timezone=$timezone" > /usr/local/etc/php/conf.d/timezone.ini

# default config
COPY ./default-config.json /etc/small-static-http.json

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=bin --filename=composer
RUN php -r "unlink('composer-setup.php');"

# setup directories
RUN mkdir /usr/src/app
WORKDIR /usr/src/app

# setup rights
RUN usermod -u $user_id www-data
RUN chown www-data:www-data /var/www
USER www-data

# entrypoint
ENTRYPOINT /usr/src/app/bin/serve