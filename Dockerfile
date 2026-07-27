FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    ca-certificates \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    libzip-dev \
    unzip \
    && update-ca-certificates \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
