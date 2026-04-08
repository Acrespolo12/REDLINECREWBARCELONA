<?php
// ═══════════════════════════════════════════
//  REDLINECREW - Edit Product Page
// ═══════════════════════════════════════════

Auth::requireLogin();
$db  = Database::getInstance();
$id  = (int)($_GET['id'] ?? 0);
if (!$id) redirect('my-products');

$product = $db->fetch("SELECT * FROM products WHERE id = ?", [$id]);
if (!$product) { flash('error', 'Anuncio no encontrado'); redirect('my-products'); }

$isOwner = (int)$product['user_id'] === (int)$_SESSION['user_id'];
if (!$isOwner && !Auth::isAdmin()) { flash('error', 'Sin permisos'); redirect('home'); }

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token inválido'); redirect('edit-product', ['id' => $id]);
    }
    $title     = trim($_POST['title'] ?? '');
    $desc      = trim($_POST['description'] ?? '');
    $price     = floatval(str_replace(',', '.', $_POST['price'] ?? 0));
    $category  = $_POST['category'] ?? '';
    $condition = $_POST['condition_type'] ?? 'bueno';
    $phone     = trim($_POST['contact_phone'] ?? '');
    $email     = trim($_POST['contact_email'] ?? '');
    $whatsapp  = trim($_POST['contact_whatsapp'] ?? '');
    $location  = trim($_POST['location'] ?? '');

    if (empty($title) || empty($desc) || $price <= 0) {
        flash('error', 'Rellena todos los campos obligatorios');
        redirect('edit-product', ['id' => $id]);
    }

    // Handle new images
    $existingImages = json_decode($product['images'] ?? '[]', true);
    $keepImages = $_POST['keep_images'] ?? [];
    $currentImages = array_values(array_filter($existingImages, fn($img) => in_array($img, $keepImages)));

    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = UPLOAD_DIR . 'products/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
            if (count($currentImages) >= 6) break;
            $file  = ['tmp_name' => $tmp, 'size' => $_FILES['images']['size'][$i], 'error' => UPLOAD_ERR_OK, 'name' => $_FILES['images']['name'][$i]];
            $valid = Security::validateImage($file);
            if (!$valid['ok']) continue;
            $ext      = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
            $filename = uniqid('prod_') . '.' . $ext;
            if (move_uploaded_file($tmp, $uploadDir . $filename)) {
                $currentImages[] = 'products/' . $filename;
            }
        }
    }

    $db->update('products', [
        'title'           => $title,
        'description'     => $desc,
        'price'           => $price,
        'category'        => $category,
        'condition_type'  => $condition,
        'contact_phone'   => $phone,
        'contact_email'   => $email,
        'contact_whatsapp'=> $whatsapp,
        'location'        => $location,
        'images'          => json_encode($currentImages),
        'status'          => Auth::isAdmin() ? $product['status'] : 'pending', // Re-review if user edits
    ], 'id = ?', [$id]);

    flash('success', 'Anuncio actualizado correctamente ✅');
    redirect('product', ['id' => $id]);
}

$images  = json_decode($product['images'] ?? '[]', true);
$pageTitle = 'Editar anuncio';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:40px;padding-bottom:60px;max-width:860px;">
  <div style="display:flex;align-items:center;gap:16px;margin-bottom:32px;">
    <a href="?page=product&id=<?= $id ?>" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Volver</a>
    <h1 style="font-family:var(--font-head);font-size:32px;font-weight:900;text-transform:uppercase;">
      Editar <span style="color:var(--red)">Anuncio</span>
    </h1>
  </div>

  <form method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

    <div class="card" style="padding:32px;">
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
        <div class="form-group">
          <label>Título *</label>
          <input type="text" name="title" class="form-control" value="<?= Security::e($product['title']) ?>" required maxlength="200" data-maxlength="200">
        </div>
        <div class="form-group">
          <label>Categoría *</label>
          <select name="category" class="form-control" required>
            <?php foreach (PRODUCT_CATEGORIES as $slug => $cat): ?>
            <option value="<?= $slug ?>" <?= $product['category']===$slug?'selected':'' ?>><?= $cat['icon'] ?> <?= Security::e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Descripción *</label>
        <textarea name="description" class="form-control" rows="5" required data-maxlength="2000"><?= Security::e($product['description']) ?></textarea>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
        <div class="form-group">
          <label>Precio (€) *</label>
          <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" required min="1" step="0.01">
        </div>
        <div class="form-group">
          <label>Estado *</label>
          <select name="condition_type" class="form-control">
            <option value="nuevo" <?= $product['condition_type']==='nuevo'?'selected':'' ?>>✨ Nuevo</option>
            <option value="como_nuevo" <?= $product['condition_type']==='como_nuevo'?'selected':'' ?>>🌟 Como nuevo</option>
            <option value="bueno" <?= $product['condition_type']==='bueno'?'selected':'' ?>>👍 Buen estado</option>
            <option value="aceptable" <?= $product['condition_type']==='aceptable'?'selected':'' ?>>👌 Aceptable</option>
          </select>
        </div>
        <div class="form-group">
          <label>Ubicación</label>
          <input type="text" name="location" class="form-control" value="<?= Security::e($product['location'] ?? '') ?>" maxlength="100">
        </div>
      </div>

      <hr class="separator">

      <!-- Current images -->
      <?php if (!empty($images)): ?>
      <div class="form-group">
        <label>Fotos actuales (desmarca las que quieres eliminar)</label>
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px;">
          <?php foreach ($images as $img): ?>
          <label style="position:relative;cursor:pointer;">
            <input type="checkbox" name="keep_images[]" value="<?= Security::e($img) ?>" checked style="position:absolute;top:4px;left:4px;z-index:2;accent-color:var(--red);">
            <img src="<?= UPLOAD_URL . Security::e($img) ?>" style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:2px solid var(--border);">
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Add new images -->
      <div class="form-group">
        <label>Añadir nuevas fotos</label>
        <div class="upload-zone" id="upload-area">
          <div class="upload-icon">📸</div>
          <p>Arrastra o haz clic para añadir más fotos</p>
          <input type="file" id="product-images" name="images[]" accept="image/*" multiple style="display:none;">
        </div>
        <div id="image-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;"></div>
      </div>

      <hr class="separator">

      <h3 style="font-family:var(--font-head);font-size:18px;font-weight:700;margin-bottom:16px;text-transform:uppercase;">
        <i class="fas fa-address-card" style="color:var(--red);"></i> Contacto
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
        <div class="form-group">
          <label><i class="fas fa-phone"></i> Teléfono</label>
          <input type="tel" name="contact_phone" class="form-control" value="<?= Security::e($product['contact_phone'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label><i class="fas fa-envelope"></i> Email</label>
          <input type="email" name="contact_email" class="form-control" value="<?= Security::e($product['contact_email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label><i class="fab fa-whatsapp" style="color:#25d366;"></i> WhatsApp</label>
          <input type="tel" name="contact_whatsapp" class="form-control" value="<?= Security::e($product['contact_whatsapp'] ?? '') ?>">
        </div>
      </div>

      <?php if (!Auth::isAdmin()): ?>
      <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Al editar tu anuncio pasará de nuevo a revisión antes de publicarse.
      </div>
      <?php endif; ?>

      <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px;">
        <a href="?page=product&id=<?= $id ?>" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-red btn-lg">
          <i class="fas fa-save"></i> Guardar cambios
        </button>
      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
