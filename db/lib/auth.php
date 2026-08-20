<?php

declare(strict_types=1);

function current_user(): ?array {
    start_app_session();
    return $_SESSION['user'] ?? null;
}

function require_login(): array {
    $user = current_user();
    if (!$user) json_response(['ok' => false, 'error' => 'Sesión no válida'], 401);
    return $user;
}

function budget_permissions(mysqli $con, int $accountId, string $username, array $legacyRoles): array {
    $permissions = [];

    $sql = "SELECT rol_presupuesto, departamento_id
            FROM presupuesto_usuario_permiso
            WHERE empleado_cuenta_id = ? AND status = 1";
    if ($st = $con->prepare($sql)) {
        $st->bind_param('i', $accountId);
        $st->execute();
        $rs = $st->get_result();
        while ($row = $rs->fetch_assoc()) {
            $permissions[] = [
                'role' => $row['rol_presupuesto'],
                'department_id' => $row['departamento_id'] !== null ? (int)$row['departamento_id'] : null,
            ];
        }
        $st->close();
    }

    $bootstrap = trim((string)env_value('BUDGET_BOOTSTRAP_ADMIN_USERNAME', ''));
    if ($bootstrap !== '' && hash_equals($bootstrap, $username)) {
        $permissions[] = ['role' => 'ADMIN', 'department_id' => null];
    }

    $codes = array_map(fn($r) => strtoupper((string)($r['codigo'] ?? '')), $legacyRoles);
    if (in_array('SUPER_ADMIN', $codes, true) || in_array('ADMIN', $codes, true)) {
        $permissions[] = ['role' => 'ADMIN', 'department_id' => null];
    }

    $unique = [];
    foreach ($permissions as $p) {
        $key = $p['role'] . ':' . ($p['department_id'] ?? '*');
        $unique[$key] = $p;
    }
    return array_values($unique);
}

function has_budget_role(array $user, array $roles, ?int $departmentId = null): bool {
    foreach ($user['budget_permissions'] ?? [] as $perm) {
        if (!in_array($perm['role'], $roles, true)) continue;
        if ($departmentId === null) {
            if ($perm['department_id'] === null) return true;
            continue;
        }
        if ($perm['department_id'] === null || (int)$perm['department_id'] === $departmentId) {
            return true;
        }
    }
    return false;
}

function require_budget_role(array $roles, ?int $departmentId = null): array {
    $user = require_login();
    if (!has_budget_role($user, $roles, $departmentId)) {
        json_response(['ok' => false, 'error' => 'No tienes permisos para esta operación'], 403);
    }
    return $user;
}
