# 🏍️ REDLINECREW — Marketplace Motero

> La comunidad motera definitiva. Compra, vende, descubre ofertas.

---

## 🚀 Instalación rápida

### Requisitos
- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.4+
- Apache con `mod_rewrite`
- Extensiones PHP: `pdo_mysql`, `fileinfo`, `simplexml`, `mbstring`

---

### Pasos

**1. Subir archivos**
```
Copia toda la carpeta `redlinecrew/` a tu servidor (ej: `/var/www/html/redlinecrew/` o `htdocs/redlinecrew/`)
```

**2. Crear base de datos**
```sql
-- En phpMyAdmin o MySQL CLI:
source /ruta/install.sql
```
O copia el contenido de `install.sql` en phpMyAdmin → SQL y ejecuta.

**3. Configurar conexión**
Edita `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
define('DB_NAME', 'redlinecrew');
define('SITE_URL', 'http://localhost/redlinecrew');
```

**4. Crear carpetas de uploads**
```bash
mkdir -p uploads/products uploads/avatars
chmod 755 uploads/ uploads/products/ uploads/avatars/
mkdir -p logs
chmod 755 logs/
```

**5. Acceder**
- **Sitio:** `http://localhost/redlinecrew/`
- **Admin:** `http://localhost/redlinecrew/admin/`
  - Email: `admin@redlinecrew.com`
  - Password: `password` *(¡cámbialo inmediatamente!)*

---

## 📋 Funcionalidades

### 🛒 Marketplace
- ✅ Publicar productos con hasta 6 fotos
- ✅ Categorías: cascos, chaquetas, motos, repuestos, etc.
- ✅ Filtros por categoría, precio, estado
- ✅ Sistema de favoritos
- ✅ Mensajería interna entre usuarios
- ✅ Valoraciones de vendedores (1-5 estrellas)
- ✅ Contacto por WhatsApp / teléfono / email

### ⚡ Ofertas automáticas
- ✅ RSS scraper de webs moteras reales
- ✅ Auto-categorización por keywords
- ✅ Ticker en tiempo real en la cabecera
- ✅ Filtro por fuente y categoría

**Fuentes incluidas:**
- Motofichas.com
- SoloMoto.es
- MotorRaider.com
- Moto1Pro.com
- RevZilla.com (Common Tread)
- Motociclismo.es

### 🤖 RedBot
- ✅ Asistente flotante en todas las páginas
- ✅ Busca productos por nombre
- ✅ Muestra últimas ofertas
- ✅ Info sobre la plataforma
- ✅ Rate limiting anti-spam
- ✅ Historial de conversaciones

### 👤 Usuarios
- ✅ Registro / login con bcrypt
- ✅ Perfil con bio, teléfono
- ✅ Mis anuncios con estados
- ✅ Favoritos
- ✅ Notificaciones
- ✅ Mensajería privada

### 🛡️ Admin Panel
- ✅ Dashboard con estadísticas
- ✅ Aprobar / rechazar anuncios
- ✅ Gestión de usuarios
- ✅ Control de ofertas
- ✅ Configuración del sitio
- ✅ Logs de actividad

### 🔒 Seguridad
- ✅ PDO con prepared statements (anti SQL Injection)
- ✅ Tokens CSRF en todos los formularios
- ✅ Bcrypt para contraseñas (coste 12)
- ✅ Rate limiting en login (5 intentos, bloqueo 15min)
- ✅ Validación MIME real de imágenes
- ✅ Sanitización XSS en todo el output
- ✅ Headers de seguridad HTTP
- ✅ `.htaccess` con protecciones

---

## ⏰ Cron Jobs (actualización automática de ofertas)

Añade esto a tu crontab (`crontab -e`):
```bash
# Actualizar ofertas cada hora
0 * * * * php /var/www/html/redlinecrew/cron_offers.php

# O llamar via URL (requiere clave):
# 0 * * * * curl "https://tudominio.com/redlinecrew/cron_offers.php?cron_key=redlinecrew2024"
```

---

## 📁 Estructura del proyecto
```
redlinecrew/
├── index.php              # Router principal
├── install.sql            # Base de datos
├── cron_offers.php        # Scraper de ofertas
├── .htaccess              # Apache config
├── includes/
│   ├── config.php         # Configuración
│   ├── bootstrap.php      # Carga todo
│   ├── db.php             # Clase Database PDO
│   ├── security.php       # Seguridad + Auth
│   ├── bot.php            # RedBot IA
│   ├── header.php         # Plantilla cabecera
│   └── footer.php         # Plantilla pie
├── pages/
│   ├── home.php           # Inicio
│   ├── products.php       # Listado productos
│   ├── product.php        # Ficha producto
│   ├── sell.php           # Publicar anuncio
│   ├── offers.php         # Ofertas externas
│   ├── search.php         # Buscador
│   ├── login.php          # Login
│   ├── register.php       # Registro
│   ├── profile.php        # Perfil usuario
│   ├── my-products.php    # Mis anuncios
│   ├── favorites.php      # Favoritos
│   ├── messages.php       # Mensajería
│   ├── notifications.php  # Notificaciones
│   ├── categories.php     # Categorías
│   ├── contact.php        # Contacto
│   └── 404.php            # Error 404
├── admin/
│   ├── index.php          # Panel admin
│   └── api.php            # API admin (AJAX)
├── api/
│   ├── bot.php            # Bot API
│   ├── search.php         # Search API
│   ├── favorites.php      # Favorites API
│   ├── offers.php         # Offers API
│   └── products.php       # Products API
└── assets/
    ├── css/main.css        # Estilos principales
    ├── js/main.js          # JavaScript
    └── img/favicon.svg    # Favicon
```

---

## 🎨 Stack tecnológico
- **Backend:** PHP 8.1 (PDO, OOP)
- **DB:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3 Variables, Vanilla JS ES6+
- **Fonts:** Barlow Condensed + Barlow + Share Tech Mono
- **Icons:** Font Awesome 6
- **Estética:** Dark Industrial / Moto (rojo #e8192c sobre negro #0a0a0c)

---

## 🔧 Personalización rápida

**Cambiar logo/colores:** `assets/css/main.css` → sección `:root`

**Añadir categorías:** `includes/config.php` → `PRODUCT_CATEGORIES`

**Añadir fuentes de ofertas:** `includes/config.php` → `OFFERS_SOURCES`

**Cambiar respuestas del bot:** `includes/bot.php` → método `processMessage()`

---

*REDLINECREW v1.0 — Hecho con ❤️ para la comunidad motera*
