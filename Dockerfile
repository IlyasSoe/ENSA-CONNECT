FROM php:8.2-cli
RUN apt-get update && apt-get install -y curl unzip
RUN docker-php-ext-install pdo pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
COPY . .
RUN composer install
RUN composer install --working-dir=chat
CMD ["php", "start.php"]
