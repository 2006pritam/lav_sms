FROM php:8.2-apache-bookworm

RUN printf '%s\n' 'error_reporting = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED' \
    > /usr/local/etc/php/conf.d/zz-render-compat.ini

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
        git \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath gd opcache pdo_mysql pdo_pgsql zip \
    && a2enmod rewrite headers \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_PROCESS_TIMEOUT=600 COMPOSER_MAX_PARALLEL_HTTP=4

WORKDIR /var/www/html
COPY . .

# Render's build network occasionally times out reaching github.com (curl error 28),
# which is where Composer downloads packages from. Retry instead of failing the deploy.
RUN set -eu; \
    n=0; \
    until composer install \
            --no-dev \
            --no-interaction \
            --prefer-dist \
            --optimize-autoloader \
            --no-scripts; do \
        n=$((n + 1)); \
        if [ "$n" -ge 5 ]; then \
            echo "composer install failed after $n attempts" >&2; \
            exit 1; \
        fi; \
        echo "composer install failed (attempt $n), retrying in $((n * 15))s..."; \
        sleep $((n * 15)); \
    done; \
    sh docker/patch-laravel-deprecations.sh; \
    php artisan package:discover --ansi; \
    mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod -R ug+rwX storage bootstrap/cache

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/render-start.sh /usr/local/bin/render-start
RUN chmod +x /usr/local/bin/render-start

ENV PORT=10000
EXPOSE 10000
CMD ["/usr/local/bin/render-start"]
