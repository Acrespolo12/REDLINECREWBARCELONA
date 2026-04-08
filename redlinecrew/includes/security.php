<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Security & Auth Functions
// ═══════════════════════════════════════════

class Security {

    // Sanitize output
    public static function e(string $str): string {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Generate CSRF token
    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Validate CSRF token
    public static function validateCsrf(string $token): bool {
        return isset($_SESSION['csrf_token']) &&
               hash_equals($_SESSION['csrf_token'], $token);
    }

    // Hash password
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }

    // Verify password
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    // Validate email
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Rate limiting (login attempts)
    public static function checkRateLimit(string $key): bool {
        $db = Database::getInstance();
        $record = $db->fetch(
            "SELECT attempts, locked_until FROM rate_limits WHERE `key` = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
            [$key]
        );
        if ($record) {
            if ($record['locked_until'] && strtotime($record['locked_until']) > time()) {
                return false; // Still locked
            }
            if ($record['attempts'] >= MAX_LOGIN_ATTEMPTS) {
                $db->update('rate_limits', ['locked_until' => date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_TIME)], '`key` = ?', [$key]);
                return false;
            }
        }
        return true;
    }

    public static function incrementRateLimit(string $key): void {
        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id FROM rate_limits WHERE `key` = ?", [$key]);
        if ($existing) {
            $db->query("UPDATE rate_limits SET attempts = attempts + 1, created_at = NOW() WHERE `key` = ?", [$key]);
        } else {
            $db->insert('rate_limits', ['key' => $key, 'attempts' => 1, 'created_at' => date('Y-m-d H:i:s')]);
        }
    }

    public static function clearRateLimit(string $key): void {
        $db = Database::getInstance();
        $db->delete('rate_limits', '`key` = ?', [$key]);
    }

    // Generate secure token
    public static function generateToken(int $bytes = 32): string {
        return bin2hex(random_bytes($bytes));
    }

    // Sanitize filename
    public static function sanitizeFilename(string $filename): string {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', basename($filename));
        return preg_replace('/\.{2,}/', '.', $filename);
    }

    // Validate image upload
    public static function validateImage(array $file): array {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'msg' => 'Error al subir el archivo'];
        }
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['ok' => false, 'msg' => 'El archivo es demasiado grande (máx 5MB)'];
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed)) {
            return ['ok' => false, 'msg' => 'Tipo de archivo no permitido'];
        }
        return ['ok' => true, 'mime' => $mime];
    }

    // XSS-safe JSON response
    public static function jsonResponse(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
        exit;
    }

    // Security headers
    public static function setSecurityHeaders(): void {
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:;");
    }
}

// ── Auth class ───────────────────────────────

class Auth {
    private static Database $db;

    private static function db(): Database {
        if (!isset(self::$db)) self::$db = Database::getInstance();
        return self::$db;
    }

    public static function login(string $email, string $password, string $ip): array {
        if (!Security::checkRateLimit($ip)) {
            return ['ok' => false, 'msg' => 'Demasiados intentos. Espera 15 minutos.'];
        }
        $user = self::db()->fetch("SELECT * FROM users WHERE email = ? AND active = 1", [strtolower(trim($email))]);
        if (!$user || !Security::verifyPassword($password, $user['password'])) {
            Security::incrementRateLimit($ip);
            return ['ok' => false, 'msg' => 'Email o contraseña incorrectos'];
        }
        Security::clearRateLimit($ip);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['username'];
        self::db()->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
        return ['ok' => true, 'role' => $user['role']];
    }

    public static function register(string $username, string $email, string $password): array {
        $username = trim($username);
        $email    = strtolower(trim($email));
        if (strlen($username) < 3 || strlen($username) > 30) return ['ok' => false, 'msg' => 'El nombre debe tener entre 3 y 30 caracteres'];
        if (!Security::validateEmail($email)) return ['ok' => false, 'msg' => 'Email inválido'];
        if (strlen($password) < 8) return ['ok' => false, 'msg' => 'La contraseña debe tener al menos 8 caracteres'];
        $existing = self::db()->fetch("SELECT id FROM users WHERE email = ? OR username = ?", [$email, $username]);
        if ($existing) return ['ok' => false, 'msg' => 'El email o nombre de usuario ya está en uso'];
        $hash = Security::hashPassword($password);
        $id = self::db()->insert('users', [
            'username'   => $username,
            'email'      => $email,
            'password'   => $hash,
            'role'       => 'user',
            'active'     => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return ['ok' => true, 'id' => $id];
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function isLoggedIn(): bool {
        return !empty($_SESSION['user_id']);
    }

    public static function isAdmin(): bool {
        return !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/?page=login');
            exit;
        }
    }

    public static function requireAdmin(): void {
        if (!self::isAdmin()) {
            header('Location: ' . SITE_URL . '/');
            exit;
        }
    }

    public static function currentUser(): ?array {
        if (!self::isLoggedIn()) return null;
        return self::db()->fetch("SELECT id, username, email, role, avatar, created_at, phone, bio FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }
}
