# Stage 1: Base
FROM php:8.4-fpm as base

RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
        libjpeg-dev \
        libpng-dev \
        libwebp-dev \
        libfreetype6-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j$(nproc) \
        intl \
        pdo \
        pdo_mysql \
        zip \
        opcache \
        gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Stage 2: Development
FROM base as development

RUN { \
    echo 'display_errors=On'; \
    echo 'error_reporting=E_ALL'; \
    echo 'memory_limit=256M'; \
    } > /usr/local/etc/php/conf.d/custom.ini

RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.validate_timestamps=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && { \
        echo 'xdebug.mode=debug'; \
        echo 'xdebug.client_host=host.docker.internal'; \
    } > /usr/local/etc/php/conf.d/xdebug.ini

ARG USER_UID
ARG USER_GID
RUN groupadd -g ${USER_GID} symfony \
    && useradd -u ${USER_UID} -g ${USER_GID} -m symfony \
    && chown -R symfony:symfony /var/www

USER symfony

# Stage 3: Production
FROM base as production

RUN { \
    echo 'display_errors=Off'; \
    echo 'log_errors=On'; \
    echo 'memory_limit=512M'; \
    } > /usr/local/etc/php/conf.d/custom.ini

RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

COPY --chown=www-data:www-data ./src /var/www/html

USER www-data
