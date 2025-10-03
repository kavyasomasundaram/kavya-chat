# Use official PHP with Apache
FROM php:8.2-apache

# Install PHP extensions for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application files
COPY . /var/www/html/

# Copy CA certificate
COPY php/certs /var/www/html/php/certs

# Expose port 80
EXPOSE 80
