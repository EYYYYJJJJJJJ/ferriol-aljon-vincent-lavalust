ARG PHP_VERSION=8.5

FROM php:${PHP_VERSION}-apache

# Install the Aiven/MySQL driver plus the local deployment fallback.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copy app files
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

# Prefer Aiven, but initialize a local SQLite database when the remote service
# is unavailable so the Lab 5 CRUD application can still start.
CMD ["bash", "-c", "php /var/www/html/scripts/init_database.php && apache2-foreground"]
