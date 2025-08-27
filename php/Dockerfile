FROM php:8.4-fpm

# Системные зависимости для Laravel
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl libonig-dev zlib1g-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath zip \
    && rm -rf /var/lib/apt/lists/*

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

WORKDIR /var/www/html