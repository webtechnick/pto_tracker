FROM php:8.4-fpm-trixie

WORKDIR /var/www

# System libraries for the PHP extensions below, plus git/unzip for Composer.
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    libzip-dev \
    libicu-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Extensions not compiled into the official image. pdo_sqlite, mbstring,
# openssl, curl, dom, fileinfo, tokenizer, ctype, xml and session are built in.
RUN docker-php-ext-install pdo_mysql bcmath intl pcntl zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# docker-compose bind-mounts the project into /var/www, so dependencies are
# installed with `./pto composer install` rather than baked into the image.
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]
