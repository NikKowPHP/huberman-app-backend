# Use official PHP 8.2 FPM image on Alpine Linux
FROM php:8.2-fpm-alpine as base

# Set environment variables
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer \
    PATH=$PATH:/composer/vendor/bin

# Arguments for user/group IDs
ARG UID=1000
ARG GID=1000

# Install system dependencies required for PHP extensions and tools
# libpng-dev, libjpeg-turbo-dev, freetype-dev for GD
# libzip-dev for zip
# postgresql-dev for pgsql/pdo_pgsql
# oniguruma-dev for mbstring (often needed on Alpine)
# build-base, autoconf for PECL installs if needed later
RUN apk update && apk add --no-cache \
    bash \
    curl \
    libxml2-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        curl \
        dom \
        gd \
        intl \
        libxml \
        mbstring \
        opcache \
        pcntl \
        pdo \
        pdo_pgsql \
        pgsql \
        zip

# Install Redis extension via PECL
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create a non-root user 'appuser' with specified UID/GID
RUN addgroup -g ${GID} appgroup && \
    adduser -u ${UID} -G appgroup -s /bin/sh -D appuser

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY --chown=appuser:appgroup . /var/www/html

# Change current user to appuser
USER appuser

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

# Development stage (can add Xdebug here if needed)
FROM base as development
# Install Xdebug (example, adjust configuration as needed)
# RUN pecl install xdebug && docker-php-ext-enable xdebug
# COPY ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
