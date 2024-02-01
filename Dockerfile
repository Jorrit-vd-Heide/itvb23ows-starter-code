# Use PHP 7.2 image as the base
FROM php:7.2

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Set the working directory
WORKDIR /src

# Copy PHP files to the container's working directory
COPY . /src

# Expose port 8000 (the port the PHP server will use)
EXPOSE 8000

# Set up PHP server to run the application
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/src"]