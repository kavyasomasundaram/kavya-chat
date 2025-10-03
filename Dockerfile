# Use official PHP with Apache
FROM php:8.2-apache

# Install PHP extensions for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application files
COPY . /var/www/html/

# Copy CA certificate
COPY php/certs /var/www/html/php/certs

# Ensure images folder exists and is writable
RUN mkdir -p /var/www/html/images && chmod -R 777 /var/www/html/images

# Expose port 80
EXPOSE 80
