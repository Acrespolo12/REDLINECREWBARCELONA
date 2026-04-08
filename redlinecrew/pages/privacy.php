<?php
$pageTitle = 'Política de Privacidad';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding-top:48px;padding-bottom:80px;max-width:800px;">
  <h1 style="font-family:var(--font-head);font-size:42px;font-weight:900;text-transform:uppercase;margin-bottom:8px;">Política de <span style="color:var(--red)">Privacidad</span></h1>
  <p style="color:var(--text-muted);margin-bottom:40px;">Última actualización: <?= date('d/m/Y') ?></p>
  <?php foreach ([
    ['¿Qué datos recogemos?','Al registrarte: nombre de usuario, email y contraseña (encriptada). Al publicar anuncios: descripción, precio, imágenes y datos de contacto opcionales. Automáticamente: dirección IP para seguridad y prevención de fraude.'],
    ['¿Para qué usamos tus datos?','Para gestionar tu cuenta, enviar notificaciones del servicio, prevenir fraude y spam, mejorar la plataforma. Nunca vendemos tus datos a terceros.'],
    ['¿Cómo protegemos tus datos?','Las contraseñas se almacenan cifradas con bcrypt. Usamos HTTPS para todas las comunicaciones. Acceso restringido a la base de datos. Actualizaciones de seguridad regulares.'],
    ['Cookies','Usamos una cookie de sesión para mantenerte conectado. No usamos cookies de rastreo ni publicidad.'],
    ['Tus derechos (RGPD)','Tienes derecho a: acceder a tus datos, rectificarlos, eliminar tu cuenta, oponerte al tratamiento. Contacta con nosotros para ejercer estos derechos.'],
    ['Conservación de datos','Tu cuenta y anuncios se conservan mientras la cuenta esté activa. Puedes eliminar tu cuenta en cualquier momento desde tu perfil.'],
    ['Menores','REDLINECREW no está dirigido a menores de 18 años. Si eres menor, no te registres.'],
    ['Contacto','Para cualquier consulta sobre privacidad: <a href="?page=contact" style="color:var(--red)">formulario de contacto</a>'],
  ] as [$title,$text]): ?>
  <div style="margin-bottom:28px;padding-bottom:28px;border-bottom:1px solid var(--border);">
    <h2 style="font-family:var(--font-head);font-size:20px;font-weight:700;margin-bottom:10px;color:var(--red);"><?= $title ?></h2>
    <p style="color:var(--text-dim);line-height:1.8;"><?= $text ?></p>
  </div>
  <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
