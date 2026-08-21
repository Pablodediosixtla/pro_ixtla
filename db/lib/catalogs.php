<?php

declare(strict_types=1);

/**
 * Catálogos ligeros para formularios interactivos.
 *
 * Se cargan durante el render de la página y se entregan al navegador como
 * JSON. Esto evita que los combos críticos dependan de peticiones AJAX
 * adicionales al cambiar de departamento y mantiene el mismo alcance que el
 * backend aplica al usuario autenticado.
 */
function ui_catalogs(array $user): array {
    $empty = [
        'loaded' => false,
        'departments' => [],
        'subitems' => [],
        'usersByDepartment' => new stdClass(),
    ];

    $db = conectar();
    if (!$db) return $empty;

    try {
        $departmentIds = visible_department_ids($db, $user);
        $departmentIds = array_values(array_unique(array_map('intval', $departmentIds)));
        $idSql = $departmentIds ? implode(',', $departmentIds) : '0';

        $departments = [];
        $sql = "SELECT departamento_id,codigo,nombre,descripcion,es_tesoreria,estatus
                FROM departamento
                WHERE estatus='ACTIVO'
                  AND departamento_id IN ($idSql)
                ORDER BY es_tesoreria DESC,nombre";
        if ($rs = $db->query($sql)) {
            while ($r = $rs->fetch_assoc()) {
                $r['departamento_id'] = (int)$r['departamento_id'];
                $r['es_tesoreria'] = (int)$r['es_tesoreria'];
                $departments[] = $r;
            }
        }

        $subitems = [];
        $sql = "SELECT s.subitem_id,s.codigo,s.nombre,s.descripcion,s.tipo,s.departamento_id,s.estatus,
                       d.nombre departamento
                FROM presupuesto_subitem s
                LEFT JOIN departamento d ON d.departamento_id=s.departamento_id
                WHERE s.estatus='ACTIVO'
                  AND (s.departamento_id IS NULL OR s.departamento_id IN ($idSql))
                ORDER BY s.tipo,s.nombre";
        if ($rs = $db->query($sql)) {
            while ($r = $rs->fetch_assoc()) {
                $r['subitem_id'] = (int)$r['subitem_id'];
                $r['departamento_id'] = $r['departamento_id'] === null ? null : (int)$r['departamento_id'];
                $subitems[] = $r;
            }
        }

        $usersByDepartment = [];
        $sql = "SELECT
                    u.usuario_id,
                    CONCAT_WS(' ',u.nombre,u.apellido_paterno,u.apellido_materno) nombre,
                    u.username,
                    u.puesto,
                    ud.departamento_id,
                    GROUP_CONCAT(DISTINCT r.nombre ORDER BY r.nombre SEPARATOR ' / ') rol
                FROM usuario_departamento ud
                JOIN usuario u
                  ON u.usuario_id=ud.usuario_id
                 AND u.estatus='ACTIVO'
                JOIN rol r
                  ON r.rol_id=ud.rol_id
                 AND r.estatus='ACTIVO'
                JOIN departamento d
                  ON d.departamento_id=ud.departamento_id
                 AND d.estatus='ACTIVO'
                WHERE ud.estatus='ACTIVO'
                  AND ud.departamento_id IN ($idSql)
                GROUP BY u.usuario_id,u.nombre,u.apellido_paterno,u.apellido_materno,u.username,u.puesto,ud.departamento_id
                ORDER BY ud.departamento_id,u.nombre,u.apellido_paterno,u.apellido_materno,u.username";
        if ($rs = $db->query($sql)) {
            $currentId = (int)($user['user_id'] ?? 0);
            while ($r = $rs->fetch_assoc()) {
                $r['usuario_id'] = (int)$r['usuario_id'];
                $r['departamento_id'] = (int)$r['departamento_id'];
                $r['is_current'] = $r['usuario_id'] === $currentId;
                $key = (string)$r['departamento_id'];
                $usersByDepartment[$key] ??= [];
                $usersByDepartment[$key][] = $r;
            }
        }

        $db->close();
        return [
            'loaded' => true,
            'departments' => $departments,
            'subitems' => $subitems,
            'usersByDepartment' => $usersByDepartment,
        ];
    } catch (Throwable $e) {
        $db->close();
        return $empty;
    }
}
