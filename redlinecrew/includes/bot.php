<?php
// ═══════════════════════════════════════════
//  REDLINECREW - RedBot (Chatbot IA)
// ═══════════════════════════════════════════

class RedBot {
    private Database $db;
    private string $sessionId;
    private ?int $userId;

    public function __construct(string $sessionId, ?int $userId = null) {
        $this->db        = Database::getInstance();
        $this->sessionId = $sessionId;
        $this->userId    = $userId;
    }

    public function respond(string $userMessage): string {
        $userMessage = trim($userMessage);
        $this->saveMessage('user', $userMessage);

        $response = $this->processMessage(strtolower($userMessage), $userMessage);
        $this->saveMessage('bot', $response);
        return $response;
    }

    private function processMessage(string $lower, string $original): string {
        // Saludos
        if (preg_match('/^(hola|buenas|hey|qué tal|ey|hi|hello)/i', $lower)) {
            $name = $this->userId ? ($_SESSION['user_name'] ?? 'rider') : 'rider';
            return "¡Hola, $name! 🏍️ Soy **RedBot**, el asistente de REDLINECREW. Puedo ayudarte a:\n\n- 🔍 **Buscar productos** de la comunidad\n- 💰 **Encontrar ofertas** actualizadas\n- 📋 **Ver categorías** disponibles\n- ❓ **Responder dudas** sobre la plataforma\n\n¿En qué te puedo ayudar?";
        }

        // Buscar productos
        if (preg_match('/busca[r]?\s+(.+)|encuentra[r]?\s+(.+)|quiero\s+(comprar|ver)\s+(.+)/i', $original, $m)) {
            $query = end(array_filter($m));
            return $this->searchProducts($query);
        }

        // Ofertas
        if (preg_match('/ofertas?|descuento|precio|barato|chollos?/i', $lower)) {
            return $this->getLatestOffers();
        }

        // Categorías
        if (preg_match('/categor[íi]as?|qué (hay|vend|tienen)|tipo[s]? de producto/i', $lower)) {
            return $this->listCategories();
        }

        // Cómo vender
        if (preg_match('/vender|publicar|poner en venta|anuncio/i', $lower)) {
            return "Para **vender en REDLINECREW** es muy fácil:\n\n1. 📝 **Regístrate** o inicia sesión\n2. Ve a **\"Vender\"** en el menú\n3. Rellena el formulario con fotos, precio y contacto\n4. ¡Un moderador lo aprobará en menos de 24h!\n\n[Ir a Vender](?page=sell) 🏍️";
        }

        // Registro/login
        if (preg_match('/registrar|crear cuenta|sign up/i', $lower)) {
            return "¡Únete a la **comunidad REDLINECREW**! 🔥\n\nRegistrarte es gratis y te permite:\n- Comprar y vender\n- Guardar favoritos\n- Recibir mensajes\n\n[Registrarse](?page=register) ➡️";
        }

        // Ayuda
        if (preg_match('/ayuda|help|problema|no (funciona|puedo|sé)|soporte/i', $lower)) {
            return "Estoy aquí para ayudarte 🛠️\n\nOpciones:\n- **Consulta el FAQ** en la página de Contacto\n- **Escríbenos** en el formulario de contacto\n- **Busca en la comunidad** con la barra de búsqueda\n\n¿Cuál es tu problema exactamente?";
        }

        // Precios / estadísticas
        if (preg_match('/estadísticas?|cuántos|total|productos disponibles/i', $lower)) {
            return $this->getSiteStats();
        }

        // Contacto
        if (preg_match('/contacto|email|correo|teléfono/i', $lower)) {
            return "📬 Puedes contactarnos en:\n\n- **Formulario:** [Página de Contacto](?page=contact)\n- **Email:** info@redlinecrew.com\n- **Instagram:** @redlinecrew\n\n¿Hay algo en lo que pueda ayudarte yo directamente?";
        }

        // Cascos (ejemplo de consulta específica)
        if (str_contains($lower, 'casco')) {
            return $this->searchProducts('casco');
        }

        // Motos
        if (str_contains($lower, 'moto')) {
            return $this->searchProducts('moto');
        }

        // Respuesta por defecto
        $suggestions = [
            "buscar cascos",
            "ver ofertas del día",
            "cómo vender mi moto",
            "ver categorías",
        ];
        $sug = array_map(fn($s) => "- \"$s\"", $suggestions);
        return "🤔 No he entendido del todo... ¡pero puedo ayudarte! Prueba con:\n\n" . implode("\n", $sug) . "\n\n¿O quieres buscar algo concreto?";
    }

    private function searchProducts(string $query): string {
        $results = $this->db->fetchAll(
            "SELECT id, title, price, category FROM products WHERE status = 'active' AND MATCH(title, description) AGAINST(? IN BOOLEAN MODE) LIMIT 5",
            [$query . '*']
        );
        if (empty($results)) {
            // Fallback LIKE
            $results = $this->db->fetchAll(
                "SELECT id, title, price, category FROM products WHERE status = 'active' AND title LIKE ? LIMIT 5",
                ['%' . $query . '%']
            );
        }
        if (empty($results)) {
            return "No encontré productos para **\"$query\"**. 😕\n\nIntenta con otros términos o [navega las categorías](?page=categories).";
        }
        $lines = ["🔍 Encontré **" . count($results) . "** productos para **\"$query\"**:\n"];
        foreach ($results as $p) {
            $price = $p['price'] ? number_format($p['price'], 0, ',', '.') . ' €' : 'Consultar';
            $lines[] = "• **{$p['title']}** — $price → [Ver](?page=product&id={$p['id']})";
        }
        $lines[] = "\n[Ver todos los resultados](?page=search&q=" . urlencode($query) . ")";
        return implode("\n", $lines);
    }

    private function getLatestOffers(): string {
        $offers = $this->db->fetchAll(
            "SELECT title, url, source, price FROM offers ORDER BY pub_date DESC LIMIT 5"
        );
        if (empty($offers)) {
            return "⚡ Aún no hay ofertas cargadas. ¡Vuelve pronto! [Ver ofertas](?page=offers)";
        }
        $lines = ["🔥 **Últimas ofertas moteras:**\n"];
        foreach ($offers as $o) {
            $price = $o['price'] ? " — **{$o['price']} €**" : '';
            $lines[] = "• {$o['title']}$price — [{$o['source']}]({$o['url']}) ↗";
        }
        $lines[] = "\n[Ver todas las ofertas](?page=offers)";
        return implode("\n", $lines);
    }

    private function listCategories(): string {
        $cats = array_values(PRODUCT_CATEGORIES);
        $lines = ["🏍️ **Categorías disponibles:**\n"];
        foreach ($cats as $slug => $cat) {
            $lines[] = "• {$cat['icon']} [{$cat['name']}](?page=categories&cat=$slug)";
        }
        return implode("\n", $lines);
    }

    private function getSiteStats(): string {
        $products = $this->db->fetch("SELECT COUNT(*) as c FROM products WHERE status = 'active'")['c'] ?? 0;
        $users    = $this->db->fetch("SELECT COUNT(*) as c FROM users WHERE active = 1")['c'] ?? 0;
        $offers   = $this->db->fetch("SELECT COUNT(*) as c FROM offers")['c'] ?? 0;
        return "📊 **REDLINECREW en números:**\n\n- 🏍️ **$products** productos activos\n- 👥 **$users** miembros registrados\n- 💰 **$offers** ofertas de webs moteras\n\n¡Únete a la comunidad! 🔥";
    }

    private function saveMessage(string $role, string $message): void {
        $this->db->insert('bot_conversations', [
            'session_id' => $this->sessionId,
            'user_id'    => $this->userId,
            'role'       => $role,
            'message'    => mb_substr($message, 0, 2000),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getHistory(): array {
        return $this->db->fetchAll(
            "SELECT role, message, created_at FROM bot_conversations WHERE session_id = ? ORDER BY created_at ASC LIMIT 50",
            [$this->sessionId]
        );
    }
}
