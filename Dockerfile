# Use the official Docker image for PHP 8.0 with an Apache web server
FROM php:8.0-apache

# Your code uses PDO to connect to the PostgreSQL database.
# This line installs the necessary PHP extensions for PDO and PostgreSQL.
RUN docker-php-ext-install pdo pdo_pgsql

# Copy all your project files into the web server's root directory
COPY . /var/www/html/
