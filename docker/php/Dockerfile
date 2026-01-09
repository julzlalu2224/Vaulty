FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    zip \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy source files
COPY src/ .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Create storage directory with proper permissions
RUN mkdir -p /var/www/storage/logs /var/www/storage/uploads /var/www/storage/cache && \
    chmod -R 775 /var/www/storage

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]