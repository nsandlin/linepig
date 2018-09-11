FROM php:7.2-apache

# Set the working directory to /var/www/html
WORKDIR /var/www/html

# Copy the Laravel directory contents into the container at /var/www/html
ADD laravel/ /var/www/html

# Make port 80 available to the world outside this container
EXPOSE 80

# Setting up new Apache root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable Apache mods
RUN a2enmod rewrite

# Set up proper permissions
RUN chown -R www-data /var/www/html/storage
RUN chown -R www-data /var/www/html/bootstrap/cache
