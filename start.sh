#!/bin/bash

# Si PORT no existe, fallback a 80
PORT=${PORT:-80}

echo "Usando puerto: $PORT"

# Reemplazar configuración de Apache en runtime
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

apache2-foreground
