FROM php:8.4-fpm

# 1. Dépendances système + extensions PHP
RUN apt-get update && apt-get install -y \
        git curl zip unzip \
        libpng-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 2. Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# 3. Utilisateur non-root identique à www-data (uid 33) déjà présent dans l'image php-fpm
#    On travaillera sous cet utilisateur pour éviter les fichiers root.

# 4. Répertoire de travail
WORKDIR /var/www

# 5. Créer les dossiers storage/bootstrap AVANT de copier le code
#    (comme ça, même si .dockerignore les exclut, ils existent)
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap \
    && chmod -R 775 storage bootstrap

# 6. Copier le code
COPY --chown=www-data:www-data . .

# 7. Installer les dépendances PHP (utilisateur www-data)
USER www-data
RUN composer install \
        --no-dev \
        --no-interaction \
        --optimize-autoloader \
        --no-scripts

# 8. Créer le lien storage + caches
USER root
RUN php artisan storage:link || true

# 9. Retour à www-data pour l'exécution
USER www-data

# 10. Port Render
ENV PORT=10000
EXPOSE 10000

# 11. Démarrage : on s'assure que les dossiers existent, puis on lance
CMD ["sh", "-c", "\
    mkdir -p storage/framework/{sessions,views,cache/data} storage/logs bootstrap/cache && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan serve --host=0.0.0.0 --port=${PORT} \
"]