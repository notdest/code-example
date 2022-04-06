FROM composer:2 as builder

RUN apk add --no-cache nodejs npm \
    && npm install --global gulp

#COPY database/ database/
#COPY composer.json composer.json
#COPY composer.lock composer.lock
#COPY package.json package.json
#COPY bootstrap/ bootstrap/
#COPY library/ library/
#COPY artisan artisan
#
#RUN  set -xe \
#     && composer install \
#    --ignore-platform-reqs \
#    --no-interaction \
#    --no-plugins \
#    --no-scripts \
#    --no-autoloader \
#    --classmap-authoritative \
#    --prefer-dist \
#    --no-dev \
#    && composer dump-autoload \
#    && npm install \
#    && find ./vendor -mindepth 2 -type d -name .git | xargs rm -rf

COPY . .
RUN  set -xe \
     && composer install --prefer-dist --ignore-platform-reqs --no-dev \
     #&& ./artisan horizon:install \
     #&& ./artisan apidoc:generate \
     #&& ./artisan passport:keys \
     && composer dump-autoload \
     && npm install \
     && find ./vendor -mindepth 2 -type d -name .git | xargs rm -rf

RUN chmod -R a+rw /app

FROM cr.yandex/crp5d1c4jf7lem7cn9aq/nginx-fpm:1.17.9-7.4-3

RUN apk add --no-cache ca-certificates fuse && rm -rf /tmp/*
#COPY docker/bin/gcsfuse /usr/local/bin
COPY docker/bin/goofys /usr/local/bin

# Set working directory
WORKDIR /var/www

# Copy Nginx config
COPY  --chown=www-data:www-data /docker/nginx/app.conf /etc/nginx/conf.d/default.conf

# Copy existing application directory contents
COPY --chown=www-data:www-data . /var/www
COPY --from=builder --chown=www-data:www-data /app/resources/ /var/www/resources/
COPY --from=builder --chown=www-data:www-data /app/vendor/ /var/www/vendor/
COPY --from=builder --chown=www-data:www-data /app/public/ /var/www/public/
COPY --from=builder --chown=www-data:www-data /app/storage/ /var/www/storage/
RUN mkdir -p /var/www/public/img_cache





