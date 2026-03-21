FROM php:8.2-cli

RUN apt-get update && apt-get install -y curl unzip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . .

RUN composer install --working-dir=chat

ENV PORT=8080

CMD ["php", "start.php"]
