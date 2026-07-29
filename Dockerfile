# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1 - build frontend assets (Vite + Tailwind) -> public/build
# ---------------------------------------------------------------------------
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build


# ---------------------------------------------------------------------------
# Stage 2 - composer dependencies (production only)
# ---------------------------------------------------------------------------
FROM composer:2.7 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev


# ---------------------------------------------------------------------------
# Stage 3 - runtime: nginx + php-fpm + supervisor
# ---------------------------------------------------------------------------
FROM php:8.2-fpm-alpine AS runtime

ARG UID=1000
ARG GID=1000

WORKDIR /var/www/html

RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        tzdata \
        mysql-client \
        fcgi

# PHP extensions (pdo_mysql for MySQL, gd/dom for barryvdh/laravel-dompdf)
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        pdo_mysql \
        bcmath \
        gd \
        zip \
        intl \
        exif \
        pcntl \
        opcache

# Run php-fpm as an unprivileged user matching the host uid/gid
RUN addgroup -g ${GID} -S app 2>/dev/null || true \
    && adduser -u ${UID} -S -D -G app app 2>/dev/null || true

# Default supervisor program toggles (dibaca sebagai %(ENV_*)s di supervisord.conf,
# jadi harus selalu ada nilainya). Bisa ditimpa lewat docker-compose.
ENV RUN_QUEUE=true \
    RUN_SCHEDULER=false \
    QUEUE_WORKERS=1

COPY docker/php/php.ini            /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/php-fpm-pool.conf  /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/nginx/nginx.conf       /etc/nginx/nginx.conf
COPY docker/nginx/default.conf     /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

# Application code (vendor/ already installed in stage 2)
COPY --from=vendor  /app                  /var/www/html
COPY --from=assets  /app/public/build     /var/www/html/public/build

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        /var/lib/nginx/tmp \
        /var/log/supervisor \
    && chown -R app:app /var/www/html storage bootstrap/cache /var/lib/nginx /var/log/nginx

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD wget -qO- http://127.0.0.1/up >/dev/null 2>&1 || wget -qO- http://127.0.0.1/ >/dev/null 2>&1 || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
