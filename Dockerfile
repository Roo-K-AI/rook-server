# syntax=docker/dockerfile:1

FROM php:8.4-fpm

# -----------------------------------------------------------
# 1. Dépendances système + extensions PHP
# -----------------------------------------------------------
RUN apt-get update && apt-get install -y \
        git curl zip unzip \
        libpng-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------------------------------------
# 2. Composer
# -----------------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# -----------------------------------------------------------
# 3. Répertoire de travail
# -----------------------------------------------------------
WORKDIR /var/www

# -----------------------------------------------------------
# 4. Créer la structure de dossiers AVANT de copier le code
# -----------------------------------------------------------
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache

# -----------------------------------------------------------
# 5. Copier le code applicatif (en root, on ajustera après)
# -----------------------------------------------------------
COPY . .

# -----------------------------------------------------------
# 6. S'assurer à nouveau que les dossiers existent
#    (au cas où .dockerignore les aurait filtrés)
# -----------------------------------------------------------
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache

# -----------------------------------------------------------
# 7. Installer les dépendances PHP en root (droits sur /var/www)
# -----------------------------------------------------------
RUN composer install \
        --no-dev \
        --no-interaction \
        --optimize-autoloader \
        --no-scripts \
    && composer clear-cache

# -----------------------------------------------------------
# 8. Créer le lien storage (idempotent)
# -----------------------------------------------------------
RUN php artisan storage:link || true

# -----------------------------------------------------------
# 9. Corriger les permissions : tout appartient à www-data
# -----------------------------------------------------------
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# -----------------------------------------------------------
# 10. Copier et préparer l'entrypoint
# -----------------------------------------------------------
COPY --chown=root:root entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# -----------------------------------------------------------
# 11. Exécution en www-data
# -----------------------------------------------------------
USER www-data

# -----------------------------------------------------------
# 12. Port Render
# -----------------------------------------------------------
ENV PORT=10000
EXPOSE 10000

# -----------------------------------------------------------
# 13. Point d'entrée
# -----------------------------------------------------------
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]