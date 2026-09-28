#!/bin/sh
set -e

echo "==> Préparation de l'environnement Laravel"

# 1. Créer les dossiers critiques au runtime (idempotent)
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# 2. Permissions (le volume Render peut casser les droits)
chown -R www-data:www-data storage bootstrap 2>/dev/null || true
chmod -R 775 storage bootstrap 2>/dev/null || true

# 3. Lien storage (ne bloque pas si déjà présent)
php artisan storage:link 2>/dev/null || true

# 4. Nettoyer les vieux caches
php artisan optimize:clear 2>/dev/null || true

# 5. Caches de production
echo "==> Caching config, routes, events"
php artisan config:cache
php artisan route:cache
php artisan event:cache

# 6. Vue cache (avec vérification)
if [ -d "storage/framework/views" ]; then
    echo "==> Caching views"
    php artisan view:cache || echo "⚠️  view:cache a échoué, on continue"
else
    echo "⚠️  storage/framework/views absent, skip view:cache"
fi

# 7. Démarrage du serveur HTTP
echo "==> Démarrage de php artisan serve sur le port ${PORT:-10000}"
exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}