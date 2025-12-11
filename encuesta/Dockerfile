FROM php:7.4-apache

COPY . /var/www/html/

# Damos permisos para que www-data pueda escribir en la carpeta de datos
RUN chown -R www-data:www-data /var/www/html