FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Install PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite

# Allow URL fopen for QR Code generation
RUN echo "allow_url_fopen = On" >> /usr/local/etc/php/conf.d/url_fopen.ini

# Set working directory
WORKDIR /var/www/html

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Copy entrypoint script
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]