FROM richarvey/nginx-php-fpm:3.1.4

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN php artisan key:generate

RUN chmod -R 777 storage bootstrap/cache
