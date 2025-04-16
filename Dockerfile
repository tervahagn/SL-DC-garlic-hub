FROM php:8.3-apache

# Install required packages  at os level
RUN apt-get update && apt-get install -y --no-install-recommends \
    libsqlite3-dev \
    mc \
    zip \
    unzip \
    libzip-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libmagickwand-dev \
    libicu-dev \
    libonig-dev


# Install required PHP extensions
RUN docker-php-ext-install pdo_sqlite \
    zip \
    simplexml \
    curl \
    intl \
    mbstring

# Install Imagick
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# workkdir in the container
WORKDIR /var/www/html

# copy only composer files (composer.json and composer.lock)
COPY composer.json composer.lock ./

# Install PHP dependencie with Composer
RUN composer install --no-dev --optimize-autoloader

# copy app files in to container without vendor folder
COPY . /var/www/html/

# Set correkten access rights
RUN chown -R www-data:www-data /var/www/html/

# Definiere die Umgebungsvariablen (falls dein CMS welche benötigt)
# ENV DATABASE_URL="sqlite:/var/www/data/database.sqlite"

# Apache-Port
EXPOSE 80
