FROM php:8.2-apache

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Copiar todos los archivos del proyecto
COPY . /var/www/html/

# Dar permisos a carpetas de uploads y logs
RUN mkdir -p /var/www/html/uploads/products \
             /var/www/html/uploads/avatars \
             /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/logs \
    && chmod -R 755 /var/www/html/uploads \
    && chmod -R 755 /var/www/html/logs

# Configurar Apache para permitir .htaccess
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/redlinecrew.conf \
    && a2enconf redlinecrew


COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]

EXPOSE 80

CMD ["apache2-foreground"]
