# Use PHP 8.2 image as the base
FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli
RUN docker-php-ext-enable mysqli

# Copy models, views, and controllers
COPY ./src /var/www/html/

# Apache gets grumpy about PID files pre-existing
RUN rm -f /usr/local/apache2/logs/httpd.pid

# Start Apache in the foreground
CMD ["apache2-foreground"]