FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Install PDO MySQL (opcional, mas útil)
RUN docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite

# Allow URL fopen for QR Code generation
RUN echo "allow_url_fopen = On" >> /usr/local/etc/php/conf.d/url_fopen.ini

# Set timezone
RUN echo "date.timezone = America/Sao_Paulo" >> /usr/local/etc/php/conf.d/timezone.ini

# Set working directory
WORKDIR /var/www/html

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Default Apache command
CMD ["apache2-foreground"]