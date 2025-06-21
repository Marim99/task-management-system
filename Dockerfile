# Use the official PHP-FPM image  
FROM php:8.2-fpm  

# Install system dependencies  
RUN apt-get update && apt-get install -y \  
    libpng-dev \  
    libjpeg-dev \  
    libfreetype6-dev \  
    zip \  
    unzip \  
    git \  
    curl \  
    libonig-dev \  
    libzip-dev \  
    && docker-php-ext-configure gd --with-freetype --with-jpeg \  
    && docker-php-ext-install pdo_mysql mbstring gd zip  

# Set working directory  
WORKDIR /var/www  

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install Composer  
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer  



# Copy existing application  
COPY . .  

# Set permissions  
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \  
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache  

# Generate application key if not exists
RUN php artisan key:generate --no-interaction || true

# Expose port 9000 for PHP-FPM  
EXPOSE 9000  

# Start PHP-FPM  
CMD ["php-fpm"]  
