# ============================================================
# Sistem Audit BSPJI — Dockerfile
# Stack: PHP 8.2 + FrankenPHP
# ============================================================
FROM dunglas/frankenphp:latest-php8.2

# Install system deps + ekstensi PHP
RUN apt-get update && apt-get install -y \
    unzip curl git \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Salin semua file proyek
COPY . /app/

# Install Composer dependencies
WORKDIR /app
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chown -R www-data:www-data /app \
    && find /app -type d -exec chmod 755 {} + \
    && find /app -type f -exec chmod 644 {} + \
    && chmod -R 775 /app/config

EXPOSE 8080
