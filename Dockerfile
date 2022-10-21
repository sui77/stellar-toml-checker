FROM composer AS composer

WORKDIR /app
COPY . /app



# run composer install to install the dependencies
RUN composer install \
  --optimize-autoloader \
  --no-interaction \
  --no-progress

RUN ls /app


FROM trafex/php-nginx

USER root

COPY --from=composer /app /var/www/html
COPY nginx.conf /etc/nginx/nginx.conf

RUN apk add --no-cache \
    php81-pecl-memcache \
    php81-pdo \
    php81-curl \
    php81-pdo_mysql \
    memcached \
    curl \
    nano

USER nobody