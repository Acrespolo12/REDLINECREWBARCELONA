<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Offers Scraper (RSS + Cron)
//  Ejecutar via cron: php cron_offers.php
//  O llamar desde el admin panel
// ═══════════════════════════════════════════

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

class OffersScraper {
    private Database $db;
    private array $stats = ['added' => 0, 'skipped' => 0, 'errors' => 0];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function run(): array {
        foreach (OFFERS_SOURCES as $source) {
            try {
                if ($source['type'] === 'rss') {
                    $this->scrapeRSS($source);
                }
            } catch (Exception $e) {
                $this->stats['errors']++;
                error_log("Scraper error [{$source['name']}]: " . $e->getMessage());
            }
        }
        // Limpiar ofertas más antiguas de 30 días
        $this->db->query("DELETE FROM offers WHERE pub_date < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        return $this->stats;
    }

    private function scrapeRSS(array $source): void {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'REDLINECREW/1.0 (+https://redlinecrew.com)',
                'follow_location' => 1,
            ]
        ]);

        $xml = @file_get_contents($source['url'], false, $ctx);
        if (!$xml) {
            $this->stats['errors']++;
            return;
        }

        libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$feed) {
            $this->stats['errors']++;
            return;
        }

        // Detectar formato (RSS 2.0 / Atom)
        $items = [];
        if (isset($feed->channel->item)) {
            $items = $feed->channel->item;
        } elseif (isset($feed->entry)) {
            $items = $feed->entry;
        }

        foreach ($items as $item) {
            $title = trim((string)$item->title);
            $url   = trim((string)($item->link ?? $item->guid ?? ''));
            if (empty($title) || empty($url)) continue;

            $desc  = strip_tags((string)($item->description ?? $item->summary ?? $item->content ?? ''));
            $desc  = mb_substr(trim($desc), 0, 1000);
            $image = $this->extractImage($item);
            $price = $this->extractPrice($title . ' ' . $desc);
            $cat   = $this->detectCategory($title . ' ' . $desc);
            $date  = date('Y-m-d H:i:s', strtotime((string)($item->pubDate ?? $item->updated ?? 'now')));

            // Verificar si ya existe
            $existing = $this->db->fetch("SELECT id FROM offers WHERE url = ?", [$url]);
            if ($existing) {
                $this->stats['skipped']++;
                continue;
            }

            $this->db->insert('offers', [
                'source'      => $source['name'],
                'title'       => mb_substr($title, 0, 500),
                'description' => $desc,
                'url'         => mb_substr($url, 0, 1000),
                'image'       => $image,
                'price'       => $price,
                'category'    => $cat,
                'pub_date'    => $date,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            $this->stats['added']++;
        }
    }

    private function extractImage(\SimpleXMLElement $item): ?string {
        // media:content
        $media = $item->children('media', true);
        if (isset($media->content)) {
            $attrs = $media->content->attributes();
            if (isset($attrs['url'])) return (string)$attrs['url'];
        }
        // enclosure
        if (isset($item->enclosure)) {
            $enc = $item->enclosure->attributes();
            if (isset($enc['type']) && strpos((string)$enc['type'], 'image') !== false) {
                return (string)$enc['url'];
            }
        }
        // Buscar img en descripción
        $desc = (string)($item->description ?? '');
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $desc, $m)) {
            return $m[1];
        }
        return null;
    }

    private function extractPrice(string $text): ?string {
        if (preg_match('/(\d{1,6}(?:[.,]\d{2})?)\s*[€$£]|[€$£]\s*(\d{1,6}(?:[.,]\d{2})?)/', $text, $m)) {
            return $m[1] ?: $m[2];
        }
        return null;
    }

    private function detectCategory(string $text): string {
        $text = strtolower($text);
        $map = [
            'cascos'    => ['casco', 'helmet', 'integral', 'modular'],
            'chaquetas' => ['chaqueta', 'jacket', 'ropa', 'traje', 'mono'],
            'guantes'   => ['guante', 'glove'],
            'botas'     => ['bota', 'boot', 'calzado'],
            'motos'     => ['moto', 'motorcycle', 'naked', 'enduro', 'trail', 'custom', 'scooter'],
            'neumaticos'=> ['neumático', 'neumatico', 'tyre', 'tire', 'rueda'],
            'escape'    => ['escape', 'exhaust', 'silencioso', 'tubo'],
            'repuestos' => ['repuesto', 'pieza', 'recambio', 'part'],
            'electronica'=> ['gps', 'intercomunicador', 'cámara', 'electrónica'],
            'lubricantes'=> ['aceite', 'lubricante', 'oil', 'fluido'],
            'maletas'   => ['maleta', 'alforja', 'bolsa', 'equipaje', 'topcase'],
        ];
        foreach ($map as $cat => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) return $cat;
            }
        }
        return 'accesorios';
    }
}

// Ejecutar si llamado directamente
if (php_sapi_name() === 'cli' || isset($_GET['cron_key']) && $_GET['cron_key'] === 'redlinecrew2024') {
    $scraper = new OffersScraper();
    $stats = $scraper->run();
    echo json_encode(['ok' => true, 'stats' => $stats, 'time' => date('Y-m-d H:i:s')]);
}
