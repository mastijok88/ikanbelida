FROM php:8.2-fpm

WORKDIR /var/www/html

COPY . .

RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

FROM php:8.2-fpm

WORKDIR /var/www/html

COPY . .

RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install zip pdo pdo_mysql gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 777 storage bootstrap/cache

CMD php artisan serve --host 0.0.0.0 --port $PORT


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 777 storage bootstrap/cache

CMD php artisan serve --host 0.0.0.0 --port $PORT

