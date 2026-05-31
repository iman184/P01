FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json ./

RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction --no-progress

FROM dunglas/frankenphp:1-php8.3

WORKDIR /app

RUN install-php-extensions pdo_mysql mysqli

COPY . /app
COPY --from=vendor /app/vendor /app/vendor

EXPOSE 8080

CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]