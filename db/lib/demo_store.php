<?php

declare(strict_types=1);

function demo_store(): array {
    start_app_session();
    if (!isset($_SESSION['demo_store']) || !is_array($_SESSION['demo_store'])) {
        $_SESSION['demo_store'] = demo_seed_data();
    }
    return $_SESSION['demo_store'];
}

function demo_store_set(array $store): void {
    start_app_session();
    $_SESSION['demo_store'] = $store;
}

function demo_reset_store(): void {
    start_app_session();
    $_SESSION['demo_store'] = demo_seed_data();
}

function demo_seed_data(): array {
    $year = (int)date('Y');
    $today = new DateTimeImmutable('today');
    $d = fn(int $days): string => $today->modify("-$days days")->format('Y-m-d');

    $employees = [
        ['id'=>1,'nombre'=>'María','apellidos'=>'González','puesto'=>'Directora de Servicios Generales'],
        ['id'=>2,'nombre'=>'Carlos','apellidos'=>'Ramírez','puesto'=>'Coordinador Administrativo'],
        ['id'=>3,'nombre'=>'Laura','apellidos'=>'Martínez','puesto'=>'Directora de Obras Públicas'],
        ['id'=>4,'nombre'=>'Jorge','apellidos'=>'Hernández','puesto'=>'Director de Seguridad Pública'],
        ['id'=>5,'nombre'=>'Ana','apellidos'=>'Torres','puesto'=>'Directora de Cultura'],
        ['id'=>6,'nombre'=>'Sofía','apellidos'=>'López','puesto'=>'Directora de Desarrollo Social'],
        ['id'=>7,'nombre'=>'Ricardo','apellidos'=>'Pérez','puesto'=>'Tesorero Municipal'],
        ['id'=>8,'nombre'=>'Daniela','apellidos'=>'Cruz','puesto'=>'Directora de Padrón y Licencias'],
    ];

    $departments = [
        ['id'=>1,'nombre'=>'Obras Públicas','descripcion'=>'Infraestructura, mantenimiento urbano y obra municipal.','director'=>3,'primera_linea'=>2,'status'=>1],
        ['id'=>2,'nombre'=>'Servicios Generales','descripcion'=>'Administración de insumos, servicios y mantenimiento institucional.','director'=>1,'primera_linea'=>2,'status'=>1],
        ['id'=>3,'nombre'=>'Seguridad Pública','descripcion'=>'Seguridad, protección y operación preventiva municipal.','director'=>4,'primera_linea'=>2,'status'=>1],
        ['id'=>4,'nombre'=>'Cultura','descripcion'=>'Programas culturales, eventos y actividades comunitarias.','director'=>5,'primera_linea'=>2,'status'=>1],
        ['id'=>5,'nombre'=>'Desarrollo Social','descripcion'=>'Programas sociales y atención a comunidades.','director'=>6,'primera_linea'=>2,'status'=>1],
        ['id'=>6,'nombre'=>'Tesorería','descripcion'=>'Administración financiera y control de recursos municipales.','director'=>7,'primera_linea'=>2,'status'=>1],
        ['id'=>7,'nombre'=>'Padrón y Licencias','descripcion'=>'Gestión de licencias, comercios y padrón municipal.','director'=>8,'primera_linea'=>2,'status'=>1],
    ];

    $budgets = [
        "$year:1"=>2600000.00,
        "$year:2"=>1200000.00,
        "$year:3"=>1850000.00,
        "$year:4"=>720000.00,
        "$year:5"=>980000.00,
        "$year:6"=>650000.00,
        "$year:7"=>520000.00,
    ];

    $subitems = [
        ['id'=>1,'departamento_id'=>null,'codigo'=>'FERRETERIA','nombre'=>'Ferretería','descripcion'=>'Materiales, herramientas y consumibles.','status'=>1],
        ['id'=>2,'departamento_id'=>null,'codigo'=>'GASOLINA','nombre'=>'Gasolina','descripcion'=>'Combustibles y lubricantes.','status'=>1],
        ['id'=>3,'departamento_id'=>null,'codigo'=>'PAPELERIA','nombre'=>'Papelería','descripcion'=>'Materiales de oficina y papelería.','status'=>1],
        ['id'=>4,'departamento_id'=>null,'codigo'=>'SERVICIOS','nombre'=>'Servicios','descripcion'=>'Servicios generales y contrataciones.','status'=>1],
        ['id'=>5,'departamento_id'=>null,'codigo'=>'MANTENIMIENTO','nombre'=>'Mantenimiento','descripcion'=>'Mantenimiento y refacciones.','status'=>1],
        ['id'=>6,'departamento_id'=>1,'codigo'=>'MATERIAL_OBRA','nombre'=>'Material de obra','descripcion'=>'Materiales para obra pública.','status'=>1],
        ['id'=>7,'departamento_id'=>4,'codigo'=>'EVENTOS','nombre'=>'Eventos culturales','descripcion'=>'Producción y logística de eventos.','status'=>1],
        ['id'=>8,'departamento_id'=>3,'codigo'=>'EQUIPO_SEG','nombre'=>'Equipo de seguridad','descripcion'=>'Equipo operativo y de protección.','status'=>1],
        ['id'=>9,'departamento_id'=>5,'codigo'=>'APOYOS','nombre'=>'Apoyos sociales','descripcion'=>'Apoyos y programas comunitarios.','status'=>1],
    ];

    $movements = [
        ['id'=>1,'folio'=>"FOL-$year-000001",'ejercicio'=>$year,'departamento_id'=>2,'subitem_id'=>1,'tipo'=>'SALIDA','fecha'=>$d(2),'monto'=>1250.00,'concepto'=>'Compra de clavos y tornillos para mantenimiento de instalaciones.','entregado_a'=>'Juan Pérez López','area_solicitante'=>'Mantenimiento','metodo_pago'=>'EFECTIVO','referencia'=>'FAC-1250','status'=>'REGISTRADO','motivo_cancelacion'=>null,'usuario_nombre'=>'Administrador Demo','files'=>[]],
        ['id'=>2,'folio'=>"FOL-$year-000002",'ejercicio'=>$year,'departamento_id'=>2,'subitem_id'=>2,'tipo'=>'SALIDA','fecha'=>$d(4),'monto'=>800.00,'concepto'=>'Carga de combustible para unidad de servicios generales.','entregado_a'=>'Operación','area_solicitante'=>'Servicios Generales','metodo_pago'=>'TARJETA','referencia'=>'TICKET-0800','status'=>'REGISTRADO','motivo_cancelacion'=>null,'usuario_nombre'=>'Administrador Demo','files'=>[]],
        ['id'=>3,'folio'=>"FOL-$year-000003",'ejercicio'=>$year,'departamento_id'=>2,'subitem_id'=>3,'tipo'=>'SALIDA','fecha'=>$d(7),'monto'=>320.00,'concepto'=>'Papelería para archivo administrativo.','entregado_a'=>'Archivo','area_solicitante'=>'Administración','metodo_pago'=>'EFECTIVO','referencia'=>'FAC-0320','status'=>'REGISTRADO','motivo_cancelacion'=>null,'usuario_nombre'=>'Administrador Demo','files'=>[]],
        ['id'=>4,'folio'=>"FOL-$year-000004",'ejercicio'=>$year,'departamento_id'=>1,'subitem_id'=>6,'tipo'=>'SALIDA','fecha'=>$d(9),'monto'=>158400.00,'concepto'=>'Material para rehabilitación de vialidad municipal.','entregado_a'=>'Supervisor de obra','area_solicitante'=>'Obra Pública','metodo_pago'=>'TRANSFERENCIA','referencia'=>'OP-2026-041','status'=>'REGISTRADO','motivo_cancelacion'=>null,'usuario_nombre'=>'Administrador Demo','files'=>[]],
        ['id'=>5,'folio'=>"FOL-$year-000005",'ejercicio'=>$year,'departamento_id'=>4,'subitem_id'=>7,'tipo'=>'SALIDA','fecha'=>$d(13),'monto'=>27500.00,'concepto'=>'Logística y sonido para evento cultural.','entregado_a'=>'Coordinación de Cultura','area_solicitante'=>'Cultura','metodo_pago'=>'TRANSFERENCIA','referencia'=>'CUL-014','status'=>'REGISTRADO','motivo_cancelacion'=>null,'usuario_nombre'=>'Administrador Demo','files'=>[]],
        ['id'=>6,'folio'=>"FOL-$year-000006",'ejercicio'=>$year,'departamento_id'=>3,'subitem_id'=>8,'tipo'=>'SALIDA','fecha'=>$d(18),'monto'=>46500.00,'concepto'=>'Equipo de protección para personal operativo.','entregado_a'=>'Almacén de Seguridad','area_solicitante'=>'Seguridad Pública','metodo_pago'=>'TRANSFERENCIA','referencia'=>'SEG-088','status'=>'REGISTRADO','motivo_cancelacion'=>null,'usuario_nombre'=>'Administrador Demo','files'=>[]],
        ['id'=>7,'folio'=>"FOL-$year-000007",'ejercicio'=>$year,'departamento_id'=>5,'subitem_id'=>9,'tipo'=>'SALIDA','fecha'=>$d(21),'monto'=>35000.00,'concepto'=>'Apoyo de programa comunitario.','entregado_a'=>'Coordinación Social','area_solicitante'=>'Desarrollo Social','metodo_pago'=>'CHEQUE','referencia'=>'DS-021','status'=>'REGISTRADO','motivo_cancelacion'=>null,'usuario_nombre'=>'Administrador Demo','files'=>[]],
        ['id'=>8,'folio'=>"FOL-$year-000008",'ejercicio'=>$year,'departamento_id'=>2,'subitem_id'=>null,'tipo'=>'ENTRADA','fecha'=>$d(25),'monto'=>45000.00,'concepto'=>'Ampliación presupuestal para servicios generales.','entregado_a'=>'','area_solicitante'=>'Tesorería','metodo_pago'=>null,'referencia'=>'AMP-002','status'=>'REGISTRADO','motivo_cancelacion'=>null,'usuario_nombre'=>'Administrador Demo','files'=>[]],
        ['id'=>9,'folio'=>"FOL-$year-000009",'ejercicio'=>$year,'departamento_id'=>1,'subitem_id'=>null,'tipo'=>'ENTRADA','fecha'=>$d(32),'monto'=>125000.00,'concepto'=>'Ampliación para programa de mantenimiento urbano.','entregado_a'=>'','area_solicitante'=>'Tesorería','metodo_pago'=>null,'referencia'=>'AMP-001','status'=>'REGISTRADO','motivo_cancelacion'=>null,'usuario_nombre'=>'Administrador Demo','files'=>[]],
        ['id'=>10,'folio'=>"FOL-$year-000010",'ejercicio'=>$year,'departamento_id'=>7,'subitem_id'=>4,'tipo'=>'SALIDA','fecha'=>$d(36),'monto'=>8400.00,'concepto'=>'Servicios de impresión para licencias y formatos.','entregado_a'=>'Padrón y Licencias','area_solicitante'=>'Padrón y Licencias','metodo_pago'=>'TRANSFERENCIA','referencia'=>'PL-010','status'=>'REGISTRADO','motivo_cancelacion'=>null,'usuario_nombre'=>'Administrador Demo','files'=>[]],
    ];

    return [
        'employees'=>$employees,
        'departments'=>$departments,
        'budgets'=>$budgets,
        'subitems'=>$subitems,
        'movements'=>$movements,
        'next_department_id'=>8,
        'next_subitem_id'=>10,
        'next_movement_id'=>11,
        'next_folio'=>11,
    ];
}

function demo_employee_name(array $store, ?int $id): string {
    if (!$id) return '';
    foreach ($store['employees'] as $e) {
        if ((int)$e['id'] === $id) return trim($e['nombre'] . ' ' . $e['apellidos']);
    }
    return '';
}

function demo_department_balance(int $departmentId, int $year): array {
    $store = demo_store();
    $assigned = (float)($store['budgets']["$year:$departmentId"] ?? 0);
    $entries = 0.0; $outputs = 0.0;
    foreach ($store['movements'] as $m) {
        if ((int)$m['departamento_id'] !== $departmentId || (int)$m['ejercicio'] !== $year || $m['status'] !== 'REGISTRADO') continue;
        if ($m['tipo'] === 'ENTRADA') $entries += (float)$m['monto'];
        if ($m['tipo'] === 'SALIDA') $outputs += (float)$m['monto'];
    }
    $available = $assigned + $entries - $outputs;
    $base = max(0.01, $assigned + $entries);
    return [
        'asignado'=>round($assigned,2),
        'entradas'=>round($entries,2),
        'salidas'=>round($outputs,2),
        'disponible'=>round($available,2),
        'ejercido_pct'=>round(($outputs / $base) * 100, 1),
    ];
}

function demo_departments(string $q = '', ?int $year = null): array {
    $store = demo_store();
    $year = $year ?: (int)date('Y');
    $q = strtolower(trim($q));
    $rows = [];
    foreach ($store['departments'] as $d) {
        if ($q !== '' && !str_contains(strtolower($d['nombre'].' '.$d['descripcion']), $q)) continue;
        $r = $d;
        $r['director_nombre'] = demo_employee_name($store, (int)($d['director'] ?? 0));
        $r['primera_linea_nombre'] = demo_employee_name($store, (int)($d['primera_linea'] ?? 0));
        $r['balance'] = demo_department_balance((int)$d['id'], $year);
        $rows[] = $r;
    }
    usort($rows, fn($a,$b)=>strcmp($a['nombre'],$b['nombre']));
    return $rows;
}

function demo_employees(): array {
    $store = demo_store();
    return $store['employees'];
}

function demo_subitems(?int $departmentId = null, bool $all = false): array {
    $store = demo_store();
    $deps = [];
    foreach ($store['departments'] as $d) $deps[(int)$d['id']] = $d['nombre'];
    $rows = [];
    foreach ($store['subitems'] as $s) {
        if (!$all && (int)$s['status'] !== 1) continue;
        $sid = $s['departamento_id'] !== null ? (int)$s['departamento_id'] : null;
        if ($departmentId && $sid !== null && $sid !== $departmentId) continue;
        $r = $s;
        $r['departamento_nombre'] = $sid ? ($deps[$sid] ?? '') : null;
        $rows[] = $r;
    }
    usort($rows, fn($a,$b)=>strcmp($a['nombre'],$b['nombre']));
    return $rows;
}

function demo_save_department(array $in): array {
    $store = demo_store();
    $id = (int)($in['id'] ?? 0);
    $name = trim((string)($in['nombre'] ?? ''));
    $description = trim((string)($in['descripcion'] ?? ''));
    $director = ($in['director'] ?? '') !== '' ? (int)$in['director'] : null;
    $line = ($in['primera_linea'] ?? '') !== '' ? (int)$in['primera_linea'] : null;
    $status = (int)($in['status'] ?? 1) === 1 ? 1 : 0;
    if ($name === '') throw new InvalidArgumentException('El nombre del departamento es obligatorio.');

    if ($id > 0) {
        $found = false;
        foreach ($store['departments'] as &$d) {
            if ((int)$d['id'] === $id) {
                $d = array_merge($d, ['nombre'=>$name,'descripcion'=>$description,'director'=>$director,'primera_linea'=>$line,'status'=>$status]);
                $found = true; break;
            }
        }
        unset($d);
        if (!$found) throw new RuntimeException('Departamento no encontrado.');
    } else {
        $id = (int)$store['next_department_id']++;
        $store['departments'][] = ['id'=>$id,'nombre'=>$name,'descripcion'=>$description,'director'=>$director,'primera_linea'=>$line,'status'=>$status];
        $store['budgets'][date('Y').":$id"] = 0.0;
    }
    demo_store_set($store);
    return ['id'=>$id];
}

function demo_save_subitem(array $in): array {
    $store = demo_store();
    $id = (int)($in['id'] ?? 0);
    $code = strtoupper(trim((string)($in['codigo'] ?? '')));
    $name = trim((string)($in['nombre'] ?? ''));
    $description = trim((string)($in['descripcion'] ?? ''));
    $departmentId = ($in['departamento_id'] ?? '') !== '' ? (int)$in['departamento_id'] : null;
    $status = (int)($in['status'] ?? 1) === 1 ? 1 : 0;
    if ($code === '' || $name === '') throw new InvalidArgumentException('Código y nombre son obligatorios.');

    if ($id > 0) {
        $found = false;
        foreach ($store['subitems'] as &$s) {
            if ((int)$s['id'] === $id) {
                $s = array_merge($s, ['codigo'=>$code,'nombre'=>$name,'descripcion'=>$description,'departamento_id'=>$departmentId,'status'=>$status]);
                $found = true; break;
            }
        }
        unset($s);
        if (!$found) throw new RuntimeException('Sub-item no encontrado.');
    } else {
        $id = (int)$store['next_subitem_id']++;
        $store['subitems'][] = ['id'=>$id,'departamento_id'=>$departmentId,'codigo'=>$code,'nombre'=>$name,'descripcion'=>$description,'status'=>$status];
    }
    demo_store_set($store);
    return ['id'=>$id];
}

function demo_save_budget(int $departmentId, int $year, float $amount): array {
    $store = demo_store();
    $store['budgets']["$year:$departmentId"] = max(0, $amount);
    demo_store_set($store);
    return demo_department_balance($departmentId, $year);
}

function demo_movement_enrich(array $m): array {
    $store = demo_store();
    $departmentName = '';
    foreach ($store['departments'] as $d) if ((int)$d['id'] === (int)$m['departamento_id']) { $departmentName = $d['nombre']; break; }
    $subName = null; $subCode = null;
    if ($m['subitem_id'] !== null) {
        foreach ($store['subitems'] as $s) if ((int)$s['id'] === (int)$m['subitem_id']) { $subName=$s['nombre']; $subCode=$s['codigo']; break; }
    }
    $m['departamento_nombre'] = $departmentName;
    $m['subitem_nombre'] = $subName;
    $m['subitem_codigo'] = $subCode;
    $m['archivos_count'] = count($m['files'] ?? []);
    return $m;
}

function demo_movements(array $filters = []): array {
    $store = demo_store();
    $year = (int)($filters['year'] ?? date('Y'));
    $departmentId = (int)($filters['departamento_id'] ?? 0);
    $type = strtoupper(trim((string)($filters['tipo'] ?? '')));
    $status = strtoupper(trim((string)($filters['status'] ?? '')));
    $q = strtolower(trim((string)($filters['q'] ?? '')));
    $from = trim((string)($filters['fecha_desde'] ?? ''));
    $to = trim((string)($filters['fecha_hasta'] ?? ''));
    $limit = max(1, min(500, (int)($filters['limit'] ?? 100)));

    $rows=[];
    foreach ($store['movements'] as $m) {
        if ((int)$m['ejercicio'] !== $year) continue;
        if ($departmentId > 0 && (int)$m['departamento_id'] !== $departmentId) continue;
        if (in_array($type,['ENTRADA','SALIDA'],true) && $m['tipo'] !== $type) continue;
        if (in_array($status,['REGISTRADO','CANCELADO'],true) && $m['status'] !== $status) continue;
        if ($from !== '' && $m['fecha'] < $from) continue;
        if ($to !== '' && $m['fecha'] > $to) continue;
        if ($q !== '') {
            $hay = strtolower(($m['folio'] ?? '').' '.($m['concepto'] ?? '').' '.($m['entregado_a'] ?? ''));
            if (!str_contains($hay,$q)) continue;
        }
        $rows[] = demo_movement_enrich($m);
    }
    usort($rows, fn($a,$b)=>strcmp($b['fecha'].$b['id'],$a['fecha'].$a['id']));
    return array_slice($rows,0,$limit);
}

function demo_movement_get(int $id): ?array {
    $store = demo_store();
    foreach ($store['movements'] as $m) if ((int)$m['id'] === $id) return demo_movement_enrich($m);
    return null;
}

function demo_create_movement(array $in, ?array $file = null): array {
    $store = demo_store();
    $departmentId = (int)($in['departamento_id'] ?? 0);
    $type = strtoupper(trim((string)($in['tipo'] ?? '')));
    $date = trim((string)($in['fecha'] ?? ''));
    $amount = money_value($in['monto'] ?? 0);
    $concept = trim((string)($in['concepto'] ?? ''));
    $subitemId = ($in['subitem_id'] ?? '') !== '' ? (int)$in['subitem_id'] : null;
    if ($departmentId <= 0 || !in_array($type,['ENTRADA','SALIDA'],true) || $date === '' || $amount <= 0 || $concept === '') {
        throw new InvalidArgumentException('Completa departamento, tipo, fecha, monto y concepto.');
    }
    if ($type === 'SALIDA' && $subitemId === null) throw new InvalidArgumentException('La salida requiere un sub-item.');
    $year = (int)substr($date,0,4);
    $balance = demo_department_balance($departmentId,$year);
    if ($type === 'SALIDA' && $amount > $balance['disponible']) throw new RuntimeException('La salida excede el presupuesto disponible.');

    $id = (int)$store['next_movement_id']++;
    $folioNo = (int)$store['next_folio']++;
    $folio = sprintf('FOL-%d-%06d',$year,$folioNo);
    $files = [];
    if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $files[] = ['id'=>1,'nombre_original'=>(string)($file['name'] ?? 'evidencia'),'mime_type'=>(string)($file['type'] ?? 'application/octet-stream'),'size_bytes'=>(int)($file['size'] ?? 0),'download_url'=>'#'];
    }
    $store['movements'][] = [
        'id'=>$id,'folio'=>$folio,'ejercicio'=>$year,'departamento_id'=>$departmentId,'subitem_id'=>$subitemId,
        'tipo'=>$type,'fecha'=>$date,'monto'=>$amount,'concepto'=>$concept,
        'entregado_a'=>trim((string)($in['entregado_a'] ?? '')),
        'area_solicitante'=>trim((string)($in['area_solicitante'] ?? '')),
        'metodo_pago'=>$type==='SALIDA' ? strtoupper(trim((string)($in['metodo_pago'] ?? 'OTRO'))) : null,
        'referencia'=>trim((string)($in['referencia'] ?? '')),
        'status'=>'REGISTRADO','motivo_cancelacion'=>null,'usuario_nombre'=>'Administrador Demo','files'=>$files,
    ];
    demo_store_set($store);
    return ['id'=>$id,'folio'=>$folio,'balance'=>demo_department_balance($departmentId,$year)];
}

function demo_cancel_movement(int $id, string $reason): bool {
    $store = demo_store();
    foreach ($store['movements'] as &$m) {
        if ((int)$m['id'] === $id && $m['status'] === 'REGISTRADO') {
            $m['status']='CANCELADO';
            $m['motivo_cancelacion']=$reason;
            demo_store_set($store);
            return true;
        }
    }
    unset($m);
    return false;
}

function demo_dashboard(int $year): array {
    $departments = demo_departments('', $year);
    $totals = ['asignado'=>0.0,'entradas'=>0.0,'salidas'=>0.0,'disponible'=>0.0];
    foreach ($departments as $d) foreach ($totals as $k=>$_) $totals[$k] += (float)$d['balance'][$k];
    foreach ($totals as $k=>$v) $totals[$k]=round($v,2);

    $monthly=[];
    for($i=1;$i<=12;$i++) $monthly[$i]=['entrada'=>0.0,'salida'=>0.0];
    foreach (demo_store()['movements'] as $m) {
        if ((int)$m['ejercicio'] !== $year || $m['status'] !== 'REGISTRADO') continue;
        $month=(int)substr($m['fecha'],5,2);
        if ($m['tipo']==='ENTRADA') $monthly[$month]['entrada'] += (float)$m['monto'];
        if ($m['tipo']==='SALIDA') $monthly[$month]['salida'] += (float)$m['monto'];
    }
    foreach($monthly as &$m){$m['entrada']=round($m['entrada'],2);$m['salida']=round($m['salida'],2);} unset($m);

    $summary=[];
    foreach($departments as $d){
        $summary[]=['id'=>$d['id'],'nombre'=>$d['nombre'],'asignado'=>$d['balance']['asignado'],'entradas'=>$d['balance']['entradas'],'salidas'=>$d['balance']['salidas'],'disponible'=>$d['balance']['disponible'],'ejercido_pct'=>$d['balance']['ejercido_pct']];
    }
    usort($summary,fn($a,$b)=>$b['asignado']<=>$a['asignado']);
    return ['totals'=>$totals,'monthly'=>$monthly,'departments'=>$summary];
}
