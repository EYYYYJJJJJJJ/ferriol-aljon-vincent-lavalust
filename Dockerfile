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

# Render assigns a PORT at runtime. Keep port 80 locally, and bind Apache to the
# assigned public port in production before applying forward-only migrations.
CMD ["bash", "-c", "APP_PORT=${PORT:-80}; sed -i \"s/^Listen .*/Listen ${APP_PORT}/\" /etc/apache2/ports.conf; sed -i \"s/<VirtualHost \*:.*>/<VirtualHost *:${APP_PORT}>/\" /etc/apache2/sites-available/000-default.conf; php /var/www/html/scripts/init_database.php && php /var/www/html/scripts/init_lab6.php && exec apache2-foreground"]
