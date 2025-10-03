# Use official PHP with Apache
FROM php:8.2-apache

# Install PHP extensions for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy all files into Apache web root
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
