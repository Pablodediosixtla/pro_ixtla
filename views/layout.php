<?php
$user = $_SESSION['user'];
$pageFile = __DIR__ . '/' . $view . '.php';
$pageTitles = [
  'dashboard'=>'Resumen presupuestal','departamentos'=>'Departamentos','departamento-detalle'=>'Detalle del departamento',
  'subitems'=>'Sub-items','movimientos'=>'Movimientos','nuevo-movimiento'=>'Nuevo movimiento','bitacora'=>'Bitácora / Historial'
];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitles[$view] ?? 'Presupuesto') ?> | Ixtlahuacán</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/presupuesto.css">
</head>
<body data-page="<?= htmlspecialchars($view) ?>">
<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <a class="brand" href="index.php">
      <span class="brand-mark small">IX</span>
      <span><strong>Presupuesto</strong><small>Ixtlahuacán</small></span>
    </a>
    <nav class="nav-list">
      <a class="<?= $view==='dashboard'?'active':'' ?>" href="index.php?view=dashboard"><span>▦</span>Resumen</a>
      <a class="<?= in_array($view,['departamentos','departamento-detalle'],true)?'active':'' ?>" href="index.php?view=departamentos"><span>▥</span>Departamentos</a>
      <a class="<?= $view==='subitems'?'active':'' ?>" href="index.php?view=subitems"><span>⌘</span>Sub-items</a>
      <a class="<?= $view==='nuevo-movimiento'?'active':'' ?>" href="index.php?view=nuevo-movimiento"><span>＋</span>Registrar movimiento</a>
      <a class="<?= $view==='movimientos'?'active':'' ?>" href="index.php?view=movimientos"><span>↕</span>Entradas / Salidas</a>
      <a class="<?= $view==='bitacora'?'active':'' ?>" href="index.php?view=bitacora"><span>≡</span>Bitácora</a>
    </nav>
    <div class="sidebar-foot">
      <div class="user-chip">
        <span class="avatar"><?= htmlspecialchars(strtoupper(substr($user['name'],0,1))) ?></span>
        <span><strong><?= htmlspecialchars($user['name']) ?></strong><small><?= htmlspecialchars($user['position'] ?: $user['username']) ?></small></span>
      </div>
      <button class="link-button" id="logoutBtn">Cerrar sesión</button>
    </div>
  </aside>

  <main class="main-area">
    <header class="topbar">
      <button class="icon-btn menu-btn" id="menuBtn" aria-label="Abrir menú">☰</button>
      <div>
        <span class="eyebrow">Presupuesto municipal · <?= date('Y') ?></span>
        <h1><?= htmlspecialchars($pageTitles[$view] ?? 'Presupuesto') ?></h1>
      </div>
      <div class="top-actions">
        <a class="btn ghost hide-mobile" href="index.php?view=bitacora">Ver bitácora</a>
        <a class="btn primary" href="index.php?view=nuevo-movimiento">+ Nuevo movimiento</a>
      </div>
    </header>
    <section class="content">
      <?php if (is_file($pageFile)) include $pageFile; ?>
    </section>
  </main>
</div>
<div id="toast" class="toast hidden"></div>
<script>window.APP_USER = <?= json_encode($user, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="js/app.js" defer></script>
<script src="js/dashboard.js" defer></script>
<script src="js/departamentos.js" defer></script>
<script src="js/subitems.js" defer></script>
<script src="js/movimientos.js" defer></script>
</body>
</html>
