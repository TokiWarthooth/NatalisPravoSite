FROM php:8.3-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копирование файлов проекта
COPY . .

# Установка зависимостей Symfony
RUN composer install --no-dev --optimize-autoloader

# Права доступа
RUN chown -R www-data:www-data /var/www/html