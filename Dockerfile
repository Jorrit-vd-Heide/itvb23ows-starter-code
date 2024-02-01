# Use PHP 8.2 image as the base
FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Install and enable pcov extension
RUN pecl install pcov \
    && docker-php-ext-enable pcov

# Set the working directory
WORKDIR /var/www/html

# Copy PHP files to the container's working directory
COPY ./src /var/www/html

# Expose port 80 (the port the PHP server will use)
EXPOSE 80

# Apache gets grumpy about PID files pre-existing
RUN rm -f /usr/local/apache2/logs/httpd.pid

# Start Apache in the foreground
CMD ["apache2-foreground"]