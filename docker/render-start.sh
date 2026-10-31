#!/bin/sh
set -eu

cd /var/www/html

PORT="${PORT:-10000}"
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY must be set in the Render environment."
    exit 1
fi

php artisan config:clear || true
php artisan config:cache
php artisan migrate --force

# Seed initial reference data and default accounts if the database is unseeded
if [ "${SKIP_DB_SEED:-false}" != "true" ]; then
    NEEDS_SEED=$(php -r '
        require "vendor/autoload.php";
        $app = require_once "bootstrap/app.php";
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        try {
            $settingsCount = Illuminate\Support\Facades\DB::table("settings")->count();
            $usersCount = Illuminate\Support\Facades\DB::table("users")->count();
            echo ($settingsCount === 0 || $usersCount === 0) ? "1" : "0";
        } catch (\Throwable $e) {
            echo "0";
        }
    ')
    if [ "$NEEDS_SEED" = "1" ]; then
        echo "Seeding initial database records..."
        php artisan db:seed --force
    fi
fi

php artisan storage:link || true
chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
