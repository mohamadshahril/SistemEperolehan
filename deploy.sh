#!/bin/bash

# Azure Post-Deployment Script for Laravel
echo "Running Laravel post-deployment setup..."

# Navigate to site root
cd /home/site/wwwroot

# Set permissions
echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache 2>/dev/null || true

# Wait a moment for files to be fully deployed
sleep 5

# Install/update composer dependencies if needed (shouldn't be needed with artifact)
# composer install --no-dev --optimize-autoloader --no-interaction

# Storage link
echo "Creating storage link..."
php artisan storage:link --force 2>/dev/null || echo "Storage link already exists or failed"

# Run migrations
echo "Running migrations..."
php artisan migrate --force --no-interaction

# Cache configuration
echo "Caching configuration..."
php artisan config:cache

# Cache routes
echo "Caching routes..."
php artisan route:cache

# Cache views
echo "Caching views..."
php artisan view:cache

# Optimize
echo "Optimizing application..."
php artisan optimize

echo "Post-deployment setup completed!"

