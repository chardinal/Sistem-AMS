FROM dunglas/frankenphp:latest-php8.2

# Install the pdo_mysql extension so the app can connect to MySQL via PDO
RUN docker-php-ext-install pdo_mysql

# Set working directory
WORKDIR /app

# Copy application code
COPY . /app

# Expose the application port
EXPOSE 8080
