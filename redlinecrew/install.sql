-- ═══════════════════════════════════════════
--  REDLINECREW - Database Schema
--  Ejecuta esto en phpMyAdmin o MySQL CLI
-- ═══════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS redlinecrew CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE redlinecrew;

-- Usuarios
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(30) NOT NULL UNIQUE,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('user','moderator','admin') DEFAULT 'user',
    active      TINYINT(1) DEFAULT 1,
    avatar      VARCHAR(255) DEFAULT NULL,
    phone       VARCHAR(20) DEFAULT NULL,
    bio         TEXT DEFAULT NULL,
    last_login  DATETIME DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin por defecto (password: Admin1234!)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@redlinecrew.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Productos
CREATE TABLE IF NOT EXISTS products (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    title        VARCHAR(200) NOT NULL,
    description  TEXT NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    category     VARCHAR(50) NOT NULL,
    condition_type ENUM('nuevo','como_nuevo','bueno','aceptable') DEFAULT 'bueno',
    images       JSON DEFAULT NULL,
    contact_phone VARCHAR(20) DEFAULT NULL,
    contact_email VARCHAR(255) DEFAULT NULL,
    contact_whatsapp VARCHAR(20) DEFAULT NULL,
    location     VARCHAR(100) DEFAULT NULL,
    status       ENUM('pending','active','sold','rejected') DEFAULT 'pending',
    views        INT UNSIGNED DEFAULT 0,
    featured     TINYINT(1) DEFAULT 0,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    FULLTEXT idx_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Favoritos
CREATE TABLE IF NOT EXISTS favorites (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fav (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Mensajes entre usuarios
CREATE TABLE IF NOT EXISTS messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT UNSIGNED NOT NULL,
    receiver_id INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED DEFAULT NULL,
    content     TEXT NOT NULL,
    is_read     TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_receiver (receiver_id),
    INDEX idx_sender (sender_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ofertas externas (scrapeadas)
CREATE TABLE IF NOT EXISTS offers (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source      VARCHAR(100) NOT NULL,
    title       VARCHAR(500) NOT NULL,
    description TEXT DEFAULT NULL,
    url         VARCHAR(1000) NOT NULL UNIQUE,
    image       VARCHAR(1000) DEFAULT NULL,
    price       VARCHAR(50) DEFAULT NULL,
    category    VARCHAR(50) DEFAULT NULL,
    pub_date    DATETIME DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_source (source),
    INDEX idx_pub_date (pub_date),
    FULLTEXT idx_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rate limiting
CREATE TABLE IF NOT EXISTS rate_limits (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`        VARCHAR(255) NOT NULL UNIQUE,
    attempts     INT UNSIGNED DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_key (`key`)
) ENGINE=InnoDB;

-- Valoraciones de productos
CREATE TABLE IF NOT EXISTS reviews (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    rating     TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment    TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_review (product_id, user_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notificaciones
CREATE TABLE IF NOT EXISTS notifications (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    type       VARCHAR(50) NOT NULL,
    message    VARCHAR(500) NOT NULL,
    link       VARCHAR(255) DEFAULT NULL,
    is_read    TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categorías personalizadas (admin puede añadir)
CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(50) NOT NULL UNIQUE,
    name        VARCHAR(100) NOT NULL,
    icon        VARCHAR(50) DEFAULT '🏍️',
    description TEXT DEFAULT NULL,
    sort_order  INT DEFAULT 0,
    active      TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar categorías por defecto
INSERT INTO categories (slug, name, icon, sort_order) VALUES
('cascos','Cascos','🪖',1),
('chaquetas','Chaquetas & Ropa','🧥',2),
('guantes','Guantes','🧤',3),
('botas','Botas','👢',4),
('motos','Motos','🏍️',5),
('accesorios','Accesorios','🔧',6),
('neumaticos','Neumáticos','⚫',7),
('escape','Escapes','💨',8),
('repuestos','Repuestos','⚙️',9),
('electronica','Electrónica','📱',10),
('lubricantes','Lubricantes & Fluidos','🛢️',11),
('maletas','Maletas & Equipaje','🎒',12);

-- Configuración del sitio
CREATE TABLE IF NOT EXISTS settings (
    `key`   VARCHAR(100) PRIMARY KEY,
    `value` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings VALUES
('site_name','REDLINECREW'),
('site_description','La comunidad motera definitiva'),
('offers_refresh_interval','3600'),
('maintenance_mode','0'),
('registration_open','1'),
('featured_products_limit','8'),
('bot_enabled','1'),
('bot_name','RedBot');

-- Logs de actividad
CREATE TABLE IF NOT EXISTS activity_logs (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED DEFAULT NULL,
    action     VARCHAR(100) NOT NULL,
    details    TEXT DEFAULT NULL,
    ip         VARCHAR(45) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Chat del bot
CREATE TABLE IF NOT EXISTS bot_conversations (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    user_id    INT UNSIGNED DEFAULT NULL,
    role       ENUM('user','bot') NOT NULL,
    message    TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
