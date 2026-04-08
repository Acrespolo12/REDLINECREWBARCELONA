<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Main Router (index.php)
// ═══════════════════════════════════════════

require_once __DIR__ . '/includes/bootstrap.php';

// Handle actions
$action = $_GET['action'] ?? '';
if ($action === 'logout') {
    Auth::logout();
    flash('info', 'Sesión cerrada correctamente');
    redirect('home');
}

// Handle POST form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';
    if (!Security::validateCsrf($csrf)) {
        flash('error', 'Token de seguridad inválido. Recarga la página.');
        redirect($_GET['page'] ?? 'home');
    }

    $db = Database::getInstance();

    switch ($formAction) {
        case 'login':
            $result = Auth::login($_POST['email'] ?? '', $_POST['password'] ?? '', $_SERVER['REMOTE_ADDR']);
            if ($result['ok']) {
                flash('success', '¡Bienvenido de vuelta!');
                redirect($result['role'] === 'admin' ? 'admin' : 'home');
            } else {
                flash('error', $result['msg']);
                redirect('login');
            }
            break;

        case 'register':
            $result = Auth::register($_POST['username'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? '');
            if ($result['ok']) {
                Auth::login($_POST['email'], $_POST['password'], $_SERVER['REMOTE_ADDR']);
                flash('success', '¡Cuenta creada! Bienvenido a REDLINECREW 🏍️');
                redirect('home');
            } else {
                flash('error', $result['msg']);
                redirect('register');
            }
            break;

        case 'sell_product':
            Auth::requireLogin();
            $title    = trim($_POST['title'] ?? '');
            $desc     = trim($_POST['description'] ?? '');
            $price    = floatval(str_replace(',', '.', $_POST['price'] ?? 0));
            $category = $_POST['category'] ?? '';
            $condition = $_POST['condition_type'] ?? 'bueno';
            $phone    = trim($_POST['contact_phone'] ?? '');
            $email    = trim($_POST['contact_email'] ?? '');
            $location = trim($_POST['location'] ?? '');

            if (empty($title) || empty($desc) || $price <= 0 || empty($category)) {
                flash('error', 'Rellena todos los campos obligatorios');
                redirect('sell');
                break;
            }

            // Handle image uploads
            $images = [];
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = UPLOAD_DIR . 'products/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                    if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $file = ['tmp_name' => $tmp, 'size' => $_FILES['images']['size'][$i], 'error' => UPLOAD_ERR_OK, 'name' => $_FILES['images']['name'][$i]];
                    $valid = Security::validateImage($file);
                    if (!$valid['ok']) continue;
                    $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                    $filename = uniqid('prod_') . '.' . strtolower($ext);
                    if (move_uploaded_file($tmp, $uploadDir . $filename)) {
                        $images[] = 'products/' . $filename;
                    }
                    if (count($images) >= 6) break;
                }
            }

            $id = $db->insert('products', [
                'user_id'        => $_SESSION['user_id'],
                'title'          => $title,
                'description'    => $desc,
                'price'          => $price,
                'category'       => $category,
                'condition_type' => $condition,
                'images'         => json_encode($images),
                'contact_phone'  => $phone,
                'contact_email'  => $email,
                'location'       => $location,
                'status'         => 'pending',
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            flash('success', '¡Anuncio publicado! Está pendiente de revisión y se activará en breve 🎉');
            redirect('my-products');
            break;

        case 'contact':
            $name    = trim($_POST['name'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $message = trim($_POST['message'] ?? '');
            if (!$name || !$email || !$message) {
                flash('error', 'Rellena todos los campos');
            } elseif (!Security::validateEmail($email)) {
                flash('error', 'Email inválido');
            } else {
                // Save to DB as notification to admin
                $db->insert('notifications', [
                    'user_id'    => 1, // Admin
                    'type'       => 'contact',
                    'message'    => "Nuevo mensaje de $name ($email): " . mb_substr($message, 0, 200),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                flash('success', '¡Mensaje enviado! Te responderemos lo antes posible.');
            }
            redirect('contact');
            break;

        case 'update_profile':
            Auth::requireLogin();
            $bio   = trim($_POST['bio'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $db->update('users', ['bio' => $bio, 'phone' => $phone], 'id = ?', [$_SESSION['user_id']]);
            flash('success', 'Perfil actualizado');
            redirect('profile');
            break;
    }
}

// Page routing
$page = preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['page'] ?? 'home'));
$pageFile = __DIR__ . "/pages/$page.php";
if (!file_exists($pageFile)) $pageFile = __DIR__ . '/pages/404.php';

// Load the page
require_once $pageFile;
