FROM php:8.2-fpm

WORKDIR "/app"

RUN apt-get update

RUN apt-get -y --no-install-recommends install \
        git \
        gcc \
        make \
        autoconf \
#        php-mysql \
#        php-pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN pecl install opentelemetry \
    && docker-php-ext-enable opentelemetry

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#CMD php composer install --ignore-platform-reqs
