FROM php:8.4.2-fpm

# Créer le fichier sources.list avec les bons dépôts HTTPS
RUN echo "deb https://deb.debian.org/debian bookworm main" > /etc/apt/sources.list && \
    echo "deb https://deb.debian.org/debian-security bookworm-security main" >> /etc/apt/sources.list && \
    echo "deb https://deb.debian.org/debian bookworm-updates main" >> /etc/apt/sources.list

# Installer et mettre à jour les certificats CA
RUN apt-get update && \
    apt-get install -y --no-install-recommends ca-certificates && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Installer les dépendances
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd


# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create Laravel directory structure
RUN mkdir -p /var/www/bootstrap/cache /var/www/storage/framework/{sessions,views,cache}

# Set working directory
WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader --no-interaction

# Copy application files
COPY . .

# Set permissions (important to do this before dump-autoload)
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/bootstrap/cache /var/www/storage

# Generate optimized autoload files
RUN composer dump-autoload --optimize

EXPOSE 9000
CMD ["php-fpm"]
