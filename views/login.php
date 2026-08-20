<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Presupuesto Ixtlahuacán | Acceso</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-body" data-auth-mode="<?= htmlspecialchars(app_auth_mode()) ?>">
  <main class="login-page">
    <section class="login-panel">
      <div class="login-brand-row">
        <img src="img/ixtla/main_logo.png" alt="Ixtlahuacán de los Membrillos" class="login-logo">
        <span class="product-pill">Presupuesto Municipal</span>
      </div>

      <div class="login-copy">
        <span class="eyebrow">Gestión financiera municipal</span>
        <h1>Control de presupuesto con trazabilidad por departamento.</h1>
        <p>Asigna recursos, registra entradas y salidas, genera folios y consulta la bitácora desde una sola plataforma.</p>
      </div>

      <?php if (auth_uses_demo()): ?>
      <div class="review-notice">
        <span class="review-dot"></span>
        <div><strong>Modo de revisión activo</strong><small>El acceso es temporal y no valida las tablas de usuarios. Los datos mostrados también pueden operar en modo demostración.</small></div>
      </div>
      <?php endif; ?>

      <form id="loginForm" class="login-form" autocomplete="on">
        <label>Usuario
          <div class="field-control"><span>◎</span><input name="username" autocomplete="username" value="pdedios" placeholder="Usuario"></div>
        </label>
        <label>Contraseña
          <div class="field-control"><span>⌁</span><input name="password" type="password" autocomplete="current-password" placeholder="Contraseña"></div>
        </label>
        <div id="loginError" class="alert error hidden"></div>
        <button class="btn primary wide login-submit" type="submit">Entrar a Presupuesto</button>
        <?php if (auth_uses_demo()): ?>
        <button class="btn review wide" id="quickReviewBtn" type="button">Entrar directamente a revisión</button>
        <?php endif; ?>
      </form>

      <div class="login-foot">
        <span>Ayuntamiento de Ixtlahuacán de los Membrillos</span>
        <span>•</span><span><?= date('Y') ?></span>
      </div>
    </section>

    <section class="login-visual" aria-label="Ixtlahuacán de los Membrillos">
      <div class="visual-glow one"></div><div class="visual-glow two"></div>
      <div class="visual-copy">
        <span class="visual-kicker">Recursos municipales</span>
        <h2>Información clara para decidir mejor.</h2>
        <p>Una experiencia visual alineada a Ixtla App, enfocada en transparencia, control y seguimiento.</p>
      </div>
      <div class="photo-stage">
        <div class="photo-main"><img src="img/ixtla/portadaLogin.png" alt="Ixtlahuacán"></div>
        <div class="floating-card budget-card"><small>Disponible</small><strong>$645,230.75</strong><span>Servicios Generales</span></div>
        <div class="floating-card trace-card"><span class="trace-icon">✓</span><div><strong>Folio generado</strong><small>Movimientos auditables</small></div></div>
        <div class="photo-small photo-a"><img src="img/ixtla/municipio_1.png" alt="Actividad municipal"></div>
        <div class="photo-small photo-b"><img src="img/ixtla/municipio_2.png" alt="Municipio de Ixtlahuacán"></div>
      </div>
      <div class="visual-stats">
        <div><strong>01</strong><span>Presupuesto</span></div>
        <div><strong>02</strong><span>Movimientos</span></div>
        <div><strong>03</strong><span>Folios</span></div>
        <div><strong>04</strong><span>Bitácora</span></div>
      </div>
    </section>
  </main>
  <script>window.APP_BOOT={authMode:<?= json_encode(app_auth_mode()) ?>,dataMode:<?= json_encode(app_data_mode()) ?>};</script>
  <script src="js/login.js"></script>
</body>
</html>
