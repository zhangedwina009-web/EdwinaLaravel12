# Dockerfile
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev zip libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring bcmath gd zip intl

# 安裝 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# ⚠️ 先複製全部專案 (artisan、config、.env.example)
COPY . .

# Composer install 只安裝 production 套件
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader

# 設定權限
RUN chown -R www-data:www-data storage bootstrap/cache

# 複製 .env.example 為 .env 並生成 APP_KEY
RUN cp .env.example .env && php artisan key:generate

# 設定權限
RUN chmod -R 777 storage bootstrap/cache


EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]