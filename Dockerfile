FROM php:8.1-cli

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY . .

CMD ["php", "-S", "0.0.0.0:10000", "-t", "/app"]
