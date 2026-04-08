FROM php:8.2.0-apache

# Extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Apache config
RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copiar SOLO el contenido real de la app
COPY ./REDLINECREWBARCELONA/ /var/www/html/

# Permisos
RUN mkdir -p /var/www/html/uploads/products \
             /var/www/html/uploads/avatars \
             /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/logs \
    && chmod -R 755 /var/www/html/uploads \
    && chmod -R 755 /var/www/html/logs

# Permitir .htaccess
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/app.conf \
    && a2enconf app

# Script de arranque (puerto dinámico Render)
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
