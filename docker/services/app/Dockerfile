FROM php:8.0-fpm

# Arguments defined in docker-compose.yml
ARG uname
ARG gid
ARG uid

# Install system dependencies
RUN apt-get update \
    && apt-get install -y \
        curl \
        dpkg-dev \
        libonig-dev \
        libxml2-dev \
        libpq-dev \
        libzip-dev \
        zip \
        unzip

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

ADD ./php.ini /usr/local/etc/php/php.ini

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pgsql bcmath

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create system user to run Composer and Artisan Commands
RUN groupadd --gid $gid $uname
RUN useradd -G www-data,root -s /bin/bash --uid $uid --gid $gid $uname

RUN mkdir -p /home/$uname/.composer && \
    chown -R $uname:$uname /home/$uname

RUN mkdir -p /opt/phpstorm-coverage && \
    chown -R $uname:$uname /opt/phpstorm-coverage

# Set working directory
WORKDIR /var/www

USER $uname

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
