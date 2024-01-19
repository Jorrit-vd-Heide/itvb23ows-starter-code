# Use PHP 5.6 image as the base
FROM php:7.2

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Set the working directory
WORKDIR /app

# Copy PHP files to the container's working directory
COPY . /app

# Expose port 8000 (the port the PHP server will use)
EXPOSE 8000

# Set up PHP server to run the application
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/app"]