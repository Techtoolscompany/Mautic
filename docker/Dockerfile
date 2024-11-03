FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    zlib1g-dev \
    libbz2-dev \
    libc-client-dev \
    libkrb5-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j$(nproc) \
    intl \
    mbstring \
    zip \
    opcache \
    gd \
    pdo \
    pdo_mysql \
    mysqli \
    bcmath \
    imap \
    sockets

RUN pecl install redis && docker-php-ext-enable redis

# install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash - && \
    apt-get install -y nodejs

# install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN a2enmod rewrite

# put custom php.ini
COPY php.ini /usr/local/etc/php/conf.d/99-custom.ini

# need permission
RUN chown -R www-data:www-data /var/www/html

CMD ["apache2-foreground"]