FROM php:8.4-cli-alpine

RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --no-scripts --no-progress --prefer-dist

COPY . ./

RUN composer dump-autoload --optimize \
    && mkdir -p var

EXPOSE 8000

CMD ["sh", "-c", "php bin/console doctrine:migrations:migrate --no-interaction && php bin/console doctrine:migrations:migrate --env=test --no-interaction && exec php -S 0.0.0.0:8000 -t public public/index.php"]
