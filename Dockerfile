FROM php:8.2-cli
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY bot.php .
ENV PHP_CLI_SERVER_WORKERS=4
CMD ["sh","-c","php bot.php setup; exec php -S 0.0.0.0:${PORT:-10000} bot.php"]

