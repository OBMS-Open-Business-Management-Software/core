FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        zlib1g-dev \
        libzip-dev \
        libonig-dev \
        zip \
        libpng-dev \
        exiftool \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libicu-dev \
        libpq-dev \
        libxpm-dev \
        libvpx-dev \
        mariadb-client \
        libxml2-dev \
        libc-client-dev \
        libkrb5-dev \
        libmagickwand-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j$(nproc) \
        zip \
        exif \
        bcmath \
        intl \
        pcntl \
        mysqli \
        pdo \
        gd \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        soap \
        opcache \
        iconv \
        imap \
        gd \
        sockets \
        sysvsem \
        sysvshm \
        calendar \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick

# Install Composer (Latest Stable)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version
