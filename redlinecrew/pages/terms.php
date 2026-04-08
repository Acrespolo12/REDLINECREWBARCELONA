<?php
$pageTitle = 'Términos de uso';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding-top:48px;padding-bottom:80px;max-width:800px;">
  <h1 style="font-family:var(--font-head);font-size:42px;font-weight:900;text-transform:uppercase;margin-bottom:8px;">Términos de <span style="color:var(--red)">Uso</span></h1>
  <p style="color:var(--text-muted);margin-bottom:40px;">Última actualización: <?= date('d/m/Y') ?></p>
  <?php foreach ([
    ['1. Aceptación','Al registrarte en REDLINECREW aceptas estos términos. Si no estás de acuerdo, no uses el servicio.'],
    ['2. Uso del servicio','REDLINECREW es un marketplace de anuncios entre particulares. No somos parte de ninguna transacción entre usuarios. El precio y condiciones de venta los fijan los vendedores.'],
    ['3. Publicación de anuncios','Los anuncios deben ser verídicos. Está prohibido publicar contenido ilegal, engañoso o que infrinja derechos de terceros. Nos reservamos el derecho de eliminar cualquier anuncio sin previo aviso.'],
    ['4. Responsabilidad','REDLINECREW no garantiza la veracidad de los anuncios publicados por usuarios. No nos hacemos responsables de las transacciones entre particulares. Actúa con precaución en cualquier compraventa.'],
    ['5. Cuentas','Eres responsable de mantener la confidencialidad de tu cuenta. Una cuenta por persona. Nos reservamos el derecho de suspender cuentas que incumplan las normas.'],
    ['6. Contenido prohibido','Queda prohibido publicar: artículos robados, armas ilegales, drogas, contenido para adultos, o cualquier producto cuya venta sea ilegal en España.'],
    ['7. Ofertas externas','Las ofertas mostradas en la sección "Ofertas" provienen de webs de terceros. No somos responsables de su contenido, disponibilidad ni precios.'],
    ['8. Modificaciones','Podemos actualizar estos términos en cualquier momento. Te notificaremos de cambios importantes.'],
  ] as [$title,$text]): ?>
  <div style="margin-bottom:28px;padding-bottom:28px;border-bottom:1px solid var(--border);">
    <h2 style="font-family:var(--font-head);font-size:20px;font-weight:700;margin-bottom:10px;color:var(--red);"><?= $title ?></h2>
    <p style="color:var(--text-dim);line-height:1.8;"><?= $text ?></p>
  </div>
  <?php endforeach; ?>
  <p style="color:var(--text-muted);font-size:14px;">Contacto: <a href="?page=contact">formulario de contacto</a></p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
