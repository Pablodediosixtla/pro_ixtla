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
      <img src="img/ixtla/main_logo.png" alt="Ixtlahuacán" class="brand-logo">
      <span class="brand-module"><strong>Presupuesto</strong><small>Gestión municipal</small></span>
    </a>
    <?php if (app_is_review_mode()): ?><div class="mode-chip"><span></span>Modo revisión</div><?php endif; ?>
    <nav class="nav-list">
      <a class="<?= $view==='dashboard'?'active':'' ?>" href="index.php?view=dashboard"><span class="nav-icon">▦</span>Resumen</a>
      <a class="<?= in_array($view,['departamentos','departamento-detalle'],true)?'active':'' ?>" href="index.php?view=departamentos"><span class="nav-icon">▥</span>Departamentos</a>
      <a class="<?= $view==='subitems'?'active':'' ?>" href="index.php?view=subitems"><span class="nav-icon">⌘</span>Sub-items</a>
      <a class="<?= $view==='nuevo-movimiento'?'active':'' ?>" href="index.php?view=nuevo-movimiento"><span class="nav-icon">＋</span>Registrar movimiento</a>
      <a class="<?= $view==='movimientos'?'active':'' ?>" href="index.php?view=movimientos"><span class="nav-icon">↕</span>Entradas / Salidas</a>
      <a class="<?= $view==='bitacora'?'active':'' ?>" href="index.php?view=bitacora"><span class="nav-icon">≡</span>Bitácora</a>
    </nav>
    <div class="sidebar-note"><strong>Control presupuestal</strong><span>Asignación → Movimiento → Folio → Bitácora</span></div>
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
        <span class="eyebrow">Ixtlahuacán de los Membrillos · <?= date('Y') ?></span>
        <h1><?= htmlspecialchars($pageTitles[$view] ?? 'Presupuesto') ?></h1>
      </div>
      <div class="top-actions">
        <?php if (app_is_review_mode()): ?><span class="top-mode">Datos de demostración</span><?php endif; ?>
        <a class="btn ghost hide-mobile" href="index.php?view=bitacora">Ver bitácora</a>
        <a class="btn primary" href="index.php?view=nuevo-movimiento">+ Nuevo movimiento</a>
      </div>
    </header>
    <div class="content">
      <?php if (is_file($pageFile)) include $pageFile; ?>
    </div>
    <footer class="app-footer"><img src="img/ixtla/main_logo_al_frente.png" alt="Ixtlahuacán al frente"><span>Plataforma interna de gestión presupuestal</span></footer>
  </main>
</div>
<div class="toast hidden" id="toast"></div>
<script>window.APP_USER=<?= json_encode($user, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="js/app.js"></script>
<?php if ($view==='dashboard'): ?><script src="js/dashboard.js"></script><?php endif; ?>
<?php if (in_array($view,['departamentos','departamento-detalle'],true)): ?><script src="js/departamentos.js"></script><?php endif; ?>
<?php if ($view==='subitems'): ?><script src="js/subitems.js"></script><?php endif; ?>
<?php if (in_array($view,['movimientos','nuevo-movimiento','bitacora'],true)): ?><script src="js/movimientos.js"></script><?php endif; ?>
</body>
</html>
