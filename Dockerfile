FROM dunglas/frankenphp

RUN install-php-extensions pdo pdo_mysql mysqli

WORKDIR /app

COPY . .