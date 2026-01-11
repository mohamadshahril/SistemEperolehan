#!/bin/bash

# Azure App Service Startup Script for Laravel
# This script runs on every app service restart

echo "Starting Laravel application setup..."

# Navigate to the app directory
cd /home/site/wwwroot

# Ensure storage and cache directories have correct permissions
echo "Setting directory permissions..."
chmod -R 775 storage bootstrap/cache

# Create symbolic link for storage if it doesn't exist
if [ ! -L public/storage ]; then
    echo "Creating storage link..."
    php artisan storage:link
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear and cache configuration
echo "Caching configuration..."
php artisan config:cache

# Clear and cache routes
echo "Caching routes..."
php artisan route:cache

# Clear and cache views
echo "Caching views..."
php artisan view:cache

# Optimize the application
echo "Optimizing application..."
php artisan optimize

echo "Laravel application setup completed successfully!"

# Start PHP-FPM (Azure will handle this, but we ensure it's ready)
echo "Application is ready to serve requests."

