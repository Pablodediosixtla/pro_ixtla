<?php
$user=$_SESSION['user'];
$perms=$user['permissions']??[];
function nav_ok(array $p,string $code):bool{return in_array($code,$p,true);}
function nav_icon(string $name): string {
    $icons=[
        'home'=>'<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M9.5 20v-6h5v6"/>',
        'building'=>'<path d="M4 21h16"/><path d="M6 21V8l6-4 6 4v13"/><path d="M9 11h.01M12 11h.01M15 11h.01M9 15h.01M12 15h.01M15 15h.01"/>',
        'users'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'shield'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
        'wallet'=>'<path d="M20 7V6a2 2 0 0 0-2-2H5a3 3 0 0 0 0 6h15v10H5a3 3 0 0 1-3-3V7"/><path d="M16 14h.01"/>',
        'tag'=>'<path d="M20.59 13.41 11 3.83V3H4v7h.83l9.58 9.59a2 2 0 0 0 2.82 0l3.36-3.36a2 2 0 0 0 0-2.82Z"/><circle cx="7.5" cy="6.5" r=".5"/>',
        'file'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h6"/>',
        'swap'=>'<path d="m7 7-4 4 4 4"/><path d="M3 11h18"/><path d="m17 3 4 4-4 4"/>',
        'chat'=>'<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/><path d="M8 9h8M8 13h5"/>',
        'audit'=>'<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
    ];
    return '<svg viewBox="0 0 24 24" aria-hidden="true">'.($icons[$name]??$icons['home']).'</svg>';
}
$viewTitles=['dashboard'=>'Resumen','departamentos'=>'Departamentos','usuarios'=>'Usuarios y jerarquía','roles'=>'Roles y permisos','presupuestos'=>'Presupuestos','subitems'=>'Sub-items','solicitudes'=>'Solicitudes','movimientos'=>'Movimientos','nuevo-movimiento'=>'Registrar movimiento','aclaraciones'=>'Aclaraciones y seguimiento','bitacora'=>'Bitácora'];
$pageTitle=$viewTitles[$view]??'Presupuesto';
$primary=$user['assignments'][0]??[];
$roleLabel=$primary['role_name']??(user_role_codes($user)[0]??'Usuario');
$departmentLabel=$primary['department']??'Alcance municipal';
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="theme-color" content="#31513f"><title><?=htmlspecialchars($pageTitle)?> | Presupuesto Ixtlahuacán</title><link rel="stylesheet" href="css/styles.css"><link rel="stylesheet" href="css/presupuesto.css"></head><body>
<div class="app-shell">
<aside class="sidebar" id="sidebar">
  <div class="sidebar-head"><img src="img/ixtla/main_logo_al_frente.png" alt="Ixtlahuacán al Frente"><button class="icon-btn mobile-only" id="closeSidebar" aria-label="Cerrar menú">✕</button></div>
  <div class="product-badge"><div><span>Presupuesto Municipal</span><small>Control · Trazabilidad · Seguimiento</small></div><span class="product-mark">PM</span></div>
  <nav class="nav-list">
    <div class="nav-section-label">GENERAL</div>
    <a class="<?=$view==='dashboard'?'active':''?>" href="?view=dashboard"><span class="nav-icon"><?=nav_icon('home')?></span><span>Resumen</span></a>
    <?php if(nav_ok($perms,'PRESUPUESTO_VER')):?><a class="<?=$view==='presupuestos'?'active':''?>" href="?view=presupuestos"><span class="nav-icon"><?=nav_icon('wallet')?></span><span>Presupuestos</span></a><?php endif;?>

    <?php if(nav_ok($perms,'SOLICITUD_CREAR')||nav_ok($perms,'SOLICITUD_APROBAR')||nav_ok($perms,'MOVIMIENTO_VER')):?><div class="nav-section-label">OPERACIÓN</div><?php endif;?>
    <?php if(nav_ok($perms,'SOLICITUD_CREAR')||nav_ok($perms,'SOLICITUD_APROBAR')):?><a class="<?=$view==='solicitudes'?'active':''?>" href="?view=solicitudes"><span class="nav-icon"><?=nav_icon('file')?></span><span>Solicitudes</span></a><?php endif;?>
    <?php if(nav_ok($perms,'MOVIMIENTO_VER')):?><a class="<?=$view==='movimientos'||$view==='nuevo-movimiento'?'active':''?>" href="?view=movimientos"><span class="nav-icon"><?=nav_icon('swap')?></span><span>Movimientos</span></a><?php endif;?>
    <?php if(nav_ok($perms,'ACLARACION_CREAR')||nav_ok($perms,'ACLARACION_GESTIONAR')):?><a class="<?=$view==='aclaraciones'?'active':''?>" href="?view=aclaraciones"><span class="nav-icon"><?=nav_icon('chat')?></span><span>Aclaraciones</span></a><?php endif;?>

    <?php if(nav_ok($perms,'DEPARTAMENTOS_GESTIONAR')||nav_ok($perms,'USUARIOS_GESTIONAR')||nav_ok($perms,'ROLES_GESTIONAR')||nav_ok($perms,'SUBITEMS_GESTIONAR')):?><div class="nav-section-label">ADMINISTRACIÓN</div><?php endif;?>
    <?php if(nav_ok($perms,'DEPARTAMENTOS_GESTIONAR')):?><a class="<?=$view==='departamentos'?'active':''?>" href="?view=departamentos"><span class="nav-icon"><?=nav_icon('building')?></span><span>Departamentos</span></a><?php endif;?>
    <?php if(nav_ok($perms,'USUARIOS_GESTIONAR')):?><a class="<?=$view==='usuarios'?'active':''?>" href="?view=usuarios"><span class="nav-icon"><?=nav_icon('users')?></span><span>Usuarios</span></a><?php endif;?>
    <?php if(nav_ok($perms,'ROLES_GESTIONAR')):?><a class="<?=$view==='roles'?'active':''?>" href="?view=roles"><span class="nav-icon"><?=nav_icon('shield')?></span><span>Roles y permisos</span></a><?php endif;?>
    <?php if(nav_ok($perms,'SUBITEMS_GESTIONAR')):?><a class="<?=$view==='subitems'?'active':''?>" href="?view=subitems"><span class="nav-icon"><?=nav_icon('tag')?></span><span>Sub-items</span></a><?php endif;?>

    <?php if(nav_ok($perms,'BITACORA_VER')):?><div class="nav-section-label">CONTROL</div><a class="<?=$view==='bitacora'?'active':''?>" href="?view=bitacora"><span class="nav-icon"><?=nav_icon('audit')?></span><span>Bitácora</span></a><?php endif;?>
  </nav>
  <div class="sidebar-foot"><div class="user-card"><div class="avatar"><?=htmlspecialchars(strtoupper(substr($user['name']??'U',0,1)))?></div><div><strong><?=htmlspecialchars($user['name']??'Usuario')?></strong><small><?=htmlspecialchars($roleLabel)?> · <?=htmlspecialchars($departmentLabel)?></small></div></div><button class="btn btn-ghost btn-sm" id="logoutBtn">Cerrar sesión</button></div>
</aside>
<div class="mobile-sidebar-backdrop" id="sidebarBackdrop"></div>
<div class="main-area">
  <header class="topbar"><button class="icon-btn menu-btn" id="openSidebar" aria-label="Abrir menú">☰</button><div class="topbar-title"><div class="topbar-brand-mini"><img src="img/ixtla/main_logo_shield.png" alt=""></div><div class="topbar-title-copy"><span class="eyebrow">PRESUPUESTO IXTLAHUACÁN</span><h1><?=htmlspecialchars($pageTitle)?></h1></div></div><div class="top-actions"><span class="scope-chip"><?=user_is_global($user)?'Vista municipal':'Mi alcance · '.htmlspecialchars($departmentLabel)?></span><?php if(nav_ok($perms,'MOVIMIENTO_SALIDA_CREAR')||nav_ok($perms,'MOVIMIENTO_ENTRADA_CREAR')):?><a class="btn btn-primary" href="?view=nuevo-movimiento">+ Movimiento</a><?php endif;?></div></header>
  <main class="content"><?php include __DIR__.'/'.$view.'.php';?></main>
  <footer class="app-footer"><span>Gobierno Municipal de Ixtlahuacán · Presupuesto Municipal</span><span>Control presupuestal con trazabilidad y auditoría.</span></footer>
</div></div>
<nav class="mobile-bottom-nav" aria-label="Navegación rápida móvil">
  <a class="<?=$view==='dashboard'?'active':''?>" href="?view=dashboard"><span>⌂</span><small>Inicio</small></a>
  <?php if(nav_ok($perms,'MOVIMIENTO_VER')):?><a class="<?=$view==='movimientos'||$view==='nuevo-movimiento'?'active':''?>" href="?view=movimientos"><span>⇄</span><small>Movimientos</small></a><?php endif;?>
  <?php if(nav_ok($perms,'SOLICITUD_CREAR')||nav_ok($perms,'SOLICITUD_APROBAR')):?><a class="<?=$view==='solicitudes'?'active':''?>" href="?view=solicitudes"><span>▤</span><small>Solicitudes</small></a><?php endif;?>
  <?php if(nav_ok($perms,'ACLARACION_CREAR')||nav_ok($perms,'ACLARACION_GESTIONAR')):?><a class="<?=$view==='aclaraciones'?'active':''?>" href="?view=aclaraciones"><span>◌</span><small>Seguimiento</small></a><?php endif;?>
</nav>
<div id="toastRoot" class="toast-root"></div><div class="modal-backdrop hidden" id="globalBackdrop"></div>
<script>window.PROIXTLA_USER=<?=json_encode($user,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;window.PROIXTLA_CSRF=<?=json_encode($_SESSION['csrf']??'')?>;</script><script src="js/app.js"></script><script src="js/<?=$view==='nuevo-movimiento'?'movimientos':$view?>.js"></script></body></html>
