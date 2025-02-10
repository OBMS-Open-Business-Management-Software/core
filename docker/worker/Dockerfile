FROM php:8.1.3-fpm

# install git
RUN apt-get update && \
        apt-get install -y --no-install-recommends git

#install some base extensions
RUN apt-get install -y \
        zlib1g-dev \
        libzip-dev \
        libonig-dev \
        zip \
        libpng-dev \
        exiftool \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libicu-dev \
        libpq-dev \
        libxpm-dev \
        libvpx-dev \
        mariadb-client \
        libxml2-dev

RUN docker-php-ext-install -j$(nproc) \
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
        iconv

# Install IMAP
RUN apt-get update && \
        apt-get install -y libc-client-dev libkrb5-dev &&  \
        rm -r /var/lib/apt/lists/*
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
        docker-php-ext-install imap

# Install Imagick
RUN apt-get update && apt-get install -y \
    libmagickwand-dev --no-install-recommends \
    && pecl install imagick \
	&& docker-php-ext-enable imagick

# Install Redis
RUN pecl install -o -f redis && \
    rm -rf /tmp/pear && \
    docker-php-ext-enable redis

# Install Composer
RUN echo "Install Composer"
RUN curl -sS https://getcomposer.org/installer | php -- --version=1.10.16 --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# install git
RUN apt-get update && \
        apt-get install -y --no-install-recommends supervisor

# start supervisor
ENTRYPOINT service supervisor start && tail -F /var/log/supervisor/supervisord.log
