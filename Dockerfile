# Use the official Docker image for PHP 8.0 with an Apache web server
FROM php:8.0-apache

# Install the PostgreSQL development libraries required by the PHP extension.
# apt-get update refreshes the package list.
# apt-get install -y libpq-dev installs the libraries.
RUN apt-get update && apt-get install -y libpq-dev

# Now that the libraries are installed, this command will succeed.
RUN docker-php-ext-install pdo pdo_pgsql

# Copy all your project files into the web server's root directory
COPY . /var/www/html/
