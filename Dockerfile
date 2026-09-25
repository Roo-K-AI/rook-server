# Étape de construction
FROM php:8.4.2-fpm AS builder

# 1. Créer les répertoires avec les bonnes permissions
RUN mkdir -p /var/www/bootstrap/cache /var/www/storage/framework/{sessions,views,cache}

# 2. Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libpq-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 3. Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# 4. Copier l’application
WORKDIR /var/www
COPY . .

# 5. Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 6. Créer le lien storage et ajuster les permissions (root)
RUN php artisan storage:link \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# Étape finale
FROM webdevops/php:8.2-alpine

# Copier depuis le builder
COPY --from=builder --chown=www-data:www-data /var/www /var/www
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

# Variables d'environnement
ENV PORT=10000
EXPOSE $PORT

# Répertoire de travail
WORKDIR /var/www

# Commande pour Render
CMD ["sh", "-c", "php artisan optimize && php artisan serve --host=0.0.0.0 --port=${PORT}"]