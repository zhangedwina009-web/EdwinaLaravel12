FROM php:8.4-fpm

# 安裝依賴
RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev unzip git \
    && docker-php-ext-install pdo_pgsql pdo_mysql zip

# 設定工作目錄
WORKDIR /var/www

# 複製 composer 文件先安裝套件
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# 複製其餘專案（不包含 .env）
COPY . .

# 設定權限
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

CMD ["php-fpm"]
