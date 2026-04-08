<?php
$pageTitle = 'Preguntas Frecuentes';
require_once __DIR__ . '/../includes/header.php';
$faqs = [
  'Publicar y vender' => [
    ['¿Cómo publico un anuncio?', 'Regístrate, haz clic en "Vender" en el menú, rellena el formulario con fotos, descripción y precio. Lo revisaremos en menos de 24 horas.'],
    ['¿Cuánto cuesta publicar?', 'Publicar en REDLINECREW es completamente gratuito. Sin comisiones ni tarifas ocultas.'],
    ['¿Cuántas fotos puedo subir?', 'Hasta 6 fotos por anuncio en formato JPG, PNG o WebP de máximo 5MB cada una.'],
    ['¿Por qué mi anuncio está pendiente?', 'Todos los anuncios pasan por una revisión manual antes de publicarse. Suele tardar menos de 24 horas.'],
    ['¿Puedo editar mi anuncio?', 'Sí, desde "Mis Anuncios" en tu perfil puedes editar cualquier anuncio activo. Al editarlo volverá a pasar revisión.'],
  ],
  'Comprar' => [
    ['¿Cómo contacto con un vendedor?', 'En la ficha del producto encontrarás los datos de contacto: WhatsApp, teléfono o email. También puedes usar la mensajería interna.'],
    ['¿REDLINECREW garantiza los productos?', 'No. Somos un marketplace de anuncios entre particulares. Recomendamos verificar el producto antes de cualquier pago.'],
    ['¿Cómo sé si un vendedor es de confianza?', 'Puedes ver las valoraciones de otros compradores en su perfil y en la ficha del producto.'],
  ],
  'Cuenta y seguridad' => [
    ['¿Cómo cambio mi contraseña?', 'Desde tu perfil, en la sección de configuración, puedes cambiar tu contraseña en cualquier momento.'],
    ['¿Cómo elimino mi cuenta?', 'Contacta con nosotros a través del formulario de contacto y eliminaremos tu cuenta y todos tus datos.'],
    ['¿Mis datos están seguros?', 'Sí. Las contraseñas se almacenan cifradas, usamos HTTPS y seguimos las mejores prácticas de seguridad web. Consulta nuestra Política de Privacidad.'],
  ],
  'Ofertas externas' => [
    ['¿De dónde vienen las ofertas?', 'Las agregamos automáticamente de webs moteras especializadas como Motofichas, SoloMoto, MotorRaider y otras.'],
    ['¿Con qué frecuencia se actualizan?', 'Las ofertas se actualizan automáticamente cada hora mediante un sistema de importación RSS.'],
    ['¿Puedo comprar desde REDLINECREW?', 'Las ofertas externas enlazan directamente a las tiendas originales. La compra se realiza en el sitio del vendedor externo.'],
  ],
];
?>
<div class="container" style="padding-top:48px;padding-bottom:80px;max-width:800px;">
  <h1 style="font-family:var(--font-head);font-size:48px;font-weight:900;text-transform:uppercase;margin-bottom:8px;">
    Preguntas <span style="color:var(--red)">Frecuentes</span>
  </h1>
  <p style="color:var(--text-muted);margin-bottom:40px;">Todo lo que necesitas saber sobre REDLINECREW</p>

  <?php foreach ($faqs as $section => $items): ?>
  <div style="margin-bottom:36px;">
    <h2 style="font-family:var(--font-head);font-size:22px;font-weight:700;text-transform:uppercase;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid var(--red);display:inline-block;"><?= $section ?></h2>
    <?php foreach ($items as [$q,$a]): ?>
    <details style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);margin-bottom:8px;overflow:hidden;">
      <summary style="padding:16px 20px;cursor:pointer;font-weight:600;font-size:15px;list-style:none;display:flex;align-items:center;justify-content:space-between;">
        <?= $q ?>
        <span style="color:var(--red);font-size:18px;flex-shrink:0;margin-left:12px;">+</span>
      </summary>
      <p style="padding:0 20px 16px;color:var(--text-dim);line-height:1.7;font-size:14px;border-top:1px solid var(--border);padding-top:14px;"><?= $a ?></p>
    </details>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>

  <div style="background:rgba(232,25,44,.06);border:1px solid var(--border-red);border-radius:var(--radius-lg);padding:28px;text-align:center;">
    <h3 style="font-family:var(--font-head);font-size:22px;font-weight:700;margin-bottom:8px;">¿No encuentras tu respuesta?</h3>
    <p style="color:var(--text-muted);margin-bottom:16px;">Contáctanos y te ayudamos en minutos.</p>
    <a href="?page=contact" class="btn btn-red"><i class="fas fa-envelope"></i> Contactar</a>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
