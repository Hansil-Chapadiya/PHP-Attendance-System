# Start with a base PHP image
FROM php:7.4-apache

# Copy all project files into the container
COPY . /var/www/html/

# Set the working directory
WORKDIR /var/www/html/

# Expose the default port
EXPOSE 80
