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
#    (garantit qu'ils existent même si .dockerignore les filtre)
# -----------------------------------------------------------
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap \
    && chmod -R 775 storage bootstrap

# -----------------------------------------------------------
# 5. Copier le code applicatif
# -----------------------------------------------------------
COPY --chown=www-data:www-data . .

# -----------------------------------------------------------
# 6. S'assurer à nouveau que les dossiers existent (au cas où
#    ils seraient absents du dépôt Git)
# -----------------------------------------------------------
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap \
    && chmod -R 775 storage bootstrap

# -----------------------------------------------------------
# 7. Installer les dépendances PHP en tant que www-data
# -----------------------------------------------------------
USER www-data
RUN composer install \
        --no-dev \
        --no-interaction \
        --optimize-autoloader \
        --no-scripts

# -----------------------------------------------------------
# 8. Créer le lien storage (idempotent)
# -----------------------------------------------------------
USER root
RUN php artisan storage:link || true

# -----------------------------------------------------------
# 9. Copier et préparer l'entrypoint
# -----------------------------------------------------------
COPY --chown=root:root entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# -----------------------------------------------------------
# 10. Retour à www-data pour l'exécution
# -----------------------------------------------------------
USER www-data

# -----------------------------------------------------------
# 11. Port exposé pour Render
# -----------------------------------------------------------
ENV PORT=10000
EXPOSE 10000

# -----------------------------------------------------------
# 12. Point d'entrée
# -----------------------------------------------------------
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]