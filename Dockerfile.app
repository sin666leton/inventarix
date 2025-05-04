FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libzip-dev \
    libpq-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libssl-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy existing application
COPY . .

# Optional: Run composer install during build (optional, bisa dipindah ke volume container kalau ingin live sync)
# RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Give necessary permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www