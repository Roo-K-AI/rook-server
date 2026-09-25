#!/bin/bash
# clear-cache.sh

echo "Nettoyage du cache Laravel..."

# Nettoyer les caches
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan event:clear

# Supprimer manuellement
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/*
rm -rf storage/framework/views/*
rm -rf storage/framework/sessions/*

echo " Cache nettoyé !"