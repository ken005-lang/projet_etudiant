#!/bin/sh
# Exit on error
set -e

# Optimize Laravel caches securely
echo "Optimizing Laravel for Docker environment..."
php artisan optimize

echo "Starting Supervisor..."
# Pass control back to CMD
exec "$@"
