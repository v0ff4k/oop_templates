FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Install shadow package to get 'usermod' and 'groupmod'
RUN apk add --no-cache shadow && \
    # Reassign or delete existing user/group using ID 33 (often 'xfs')
    if getent passwd 33; then deluser $(getent passwd 33 | cut -d: -f1); fi && \
    if getent group 33; then delgroup $(getent group 33 | cut -d: -f1); fi && \
    # Modify www-data to use 33:33
    usermod -u 33 www-data && \
    groupmod -g 33 www-data


# Install Alpine system packages
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip


# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Use standard production configurations
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"


# Переключение на пользователя www-data (UID=33, стандартный для PHP-FPM)
USER www-data

EXPOSE 9000

CMD ["php-fpm"]
