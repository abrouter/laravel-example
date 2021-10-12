#!/usr/bin/env bash

# Exit the script if any statement returns a non-true return value
set -e

cd /app
php artisan cache:clear
php artisan migrate --force
COMPOSER_ALLOW_SUPERUSER=1 /usr/local/bin/composer dump-autoload
/etc/init.d/php7.1-fpm reload
