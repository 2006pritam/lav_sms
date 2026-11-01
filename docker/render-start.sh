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
php artisan route:clear || true
php artisan view:clear || true
php artisan config:cache
php artisan migrate --force

# Seed initial reference data and default accounts if reference tables are empty
if [ "${SKIP_DB_SEED:-false}" != "true" ]; then
    NEEDS_SEED=$(php -r '
        require "vendor/autoload.php";
        $app = require_once "bootstrap/app.php";
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        try {
            $settingsCount = Illuminate\Support\Facades\DB::table("settings")->count();
            $userTypesCount = Illuminate\Support\Facades\DB::table("user_types")->count();
            $classTypesCount = Illuminate\Support\Facades\DB::table("class_types")->count();
            echo ($settingsCount === 0 || $userTypesCount === 0 || $classTypesCount === 0) ? "1" : "0";
        } catch (\Throwable $e) {
            echo "0";
        }
    ')
    if [ "$NEEDS_SEED" = "1" ]; then
        echo "Seeding initial database reference records..."
        php artisan db:seed --force
    fi
fi

# Multi-school: make sure a default school exists and that all existing/seeded data belongs to it
php artisan school:backfill

# Ensure attendances table, admin@gmail.com (admin@1234), and PostgreSQL sequences are in sync
php -r '
    require "vendor/autoload.php";
    $app = require_once "bootstrap/app.php";
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    try {
        if (file_exists("database/migrations/2026_10_31_000002_create_attendances_table.php")) {
            require_once "database/migrations/2026_10_31_000002_create_attendances_table.php";
            (new CreateAttendancesTable())->up();
        }
        $hash = Illuminate\Support\Facades\Hash::make("admin@1234");
        $byEmail = Illuminate\Support\Facades\DB::table("users")->where("email", "admin@gmail.com")->first();
        if ($byEmail) {
            Illuminate\Support\Facades\DB::table("users")->where("id", $byEmail->id)->update([
                "password" => $hash,
                "user_type" => "super_admin",
                "updated_at" => now(),
            ]);
        } else {
            $byUser = Illuminate\Support\Facades\DB::table("users")->where("username", "admin")->first();
            if ($byUser) {
                Illuminate\Support\Facades\DB::table("users")->where("id", $byUser->id)->update([
                    "email" => "admin@gmail.com",
                    "password" => $hash,
                    "user_type" => "super_admin",
                    "updated_at" => now(),
                ]);
            } else {
                Illuminate\Support\Facades\DB::table("users")->insert([
                    "name" => "Admin",
                    "email" => "admin@gmail.com",
                    "username" => "admin",
                    "password" => $hash,
                    "user_type" => "super_admin",
                    "code" => strtoupper(Illuminate\Support\Str::random(10)),
                    "photo" => App\Helpers\Qs::getDefaultUserImage(),
                    "remember_token" => Illuminate\Support\Str::random(10),
                    "created_at" => now(),
                    "updated_at" => now(),
                ]);
            }
        }
        if (Illuminate\Support\Facades\DB::getDriverName() === "pgsql") {
            $tables = ["states", "lgas", "nationalities", "blood_groups", "user_types", "class_types", "my_classes", "sections", "settings", "dorms", "grades", "skills", "users", "subjects", "student_records", "exams", "marks", "exam_records", "payments", "payment_records", "receipts", "time_table_records", "time_slots", "time_tables", "attendances", "schools"];
            foreach ($tables as $t) {
                if (Illuminate\Support\Facades\Schema::hasTable($t)) {
                    Illuminate\Support\Facades\DB::statement("SELECT setval(pg_get_serial_sequence(\x27{$t}\x27, \x27id\x27), COALESCE((SELECT MAX(id) FROM {$t}), 1), true)");
                }
            }
        }
    } catch (\Throwable $e) {
        fwrite(STDERR, "Startup bootstrap warning: " . $e->getMessage() . "\n");
    }
'

php artisan storage:link || true
chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
