<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Presupuesto Ixtlahuacán | <?= htmlspecialchars($pageTitle ?? 'Acceso') ?></title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-body">
  <main class="login-shell">
    <section class="login-brand-panel">
      <div class="brand-mark">IX</div>
      <div>
        <span class="eyebrow">Gobierno municipal</span>
        <h1>Presupuesto<br>Ixtlahuacán</h1>
        <p>Control de asignaciones, entradas, salidas, folios y trazabilidad por departamento.</p>
      </div>
      <div class="login-mini-flow">
        <span>Departamento</span><b>→</b><span>Movimiento</span><b>→</b><span>Folio</span><b>→</b><span>Bitácora</span>
      </div>
    </section>
    <section class="login-card">
      <div class="mobile-brand"><span class="brand-mark small">IX</span><strong>Presupuesto Ixtlahuacán</strong></div>
      <span class="eyebrow">Acceso seguro</span>
      <h2>Iniciar sesión</h2>
      <p class="muted">Usa tu cuenta municipal registrada.</p>
      <form id="loginForm" class="stack-form" autocomplete="on">
        <label>Usuario
          <input type="text" name="username" autocomplete="username" required placeholder="Usuario">
        </label>
        <label>Contraseña
          <input type="password" name="password" autocomplete="current-password" required placeholder="••••••••">
        </label>
        <div id="loginError" class="alert error hidden"></div>
        <button class="btn primary wide" type="submit">Entrar a Presupuesto</button>
      </form>
      <p class="login-foot">Plataforma de gestión interna · Ixtlahuacán</p>
    </section>
  </main>
  <script src="js/login.js" defer></script>
</body>
</html>
