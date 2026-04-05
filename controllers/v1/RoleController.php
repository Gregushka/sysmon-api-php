<?php

class RoleController
{
    // ── Roles ────────────────────────────────────────────────────────────────

    public static function getRoles(array $params, ?array $user): never
    {
        $repo  = new RoleRepository();
        $roles = array_map(fn($r) => $r->toArray(), $repo->findAll());
        ResponseHelper::send(['roles' => $roles]);
    }

    public static function createRole(array $params, ?array $user): never
    {
        $data = ValidationHelper::parseJsonBody();
        ValidationHelper::requireFields($data, ['name']);

        $repo  = new RoleRepository();
        $newId = $repo->create($data);
        $role  = $repo->findById($newId);

        Logger::logRequest('POST', '/roles', $data, $user, $role?->toArray(), 201, 'role', 'create');
        ResponseHelper::send(['status_code' => 0, 'role' => $role?->toArray()], 201);
    }

    public static function updateRole(array $params, ?array $user): never
    {
        $roleId = ValidationHelper::requirePositiveInt($params['role_id'] ?? 0, 'role_id');
        $data   = ValidationHelper::parseJsonBody();

        $repo   = new RoleRepository();
        $before = $repo->findById($roleId)?->toArray();
        if ($before === null) {
            ResponseHelper::notFound('Role not found');
        }

        $repo->update($roleId, $data);
        $after = $repo->findById($roleId)?->toArray();

        Logger::logRequest('PUT', '/roles/' . $roleId, $data, $user, $after, 200, 'role', 'update', $before, $after);
        ResponseHelper::send(['status_code' => 0, 'role' => $after]);
    }

    public static function deleteRole(array $params, ?array $user): never
    {
        $roleId = ValidationHelper::requirePositiveInt($params['role_id'] ?? 0, 'role_id');
        $repo   = new RoleRepository();
        $before = $repo->findById($roleId)?->toArray();
        if ($before === null) {
            ResponseHelper::notFound('Role not found');
        }

        $repo->delete($roleId);

        Logger::logRequest('DELETE', '/roles/' . $roleId, [], $user, null, 200, 'role', 'delete', $before, null);
        ResponseHelper::send(['status_code' => 0, 'message' => 'Role deleted']);
    }

    // ── Groups ───────────────────────────────────────────────────────────────

    public static function getGroups(array $params, ?array $user): never
    {
        $repo   = new GroupRepository();
        $groups = array_map(fn($g) => $g->toArray(), $repo->findAll());
        ResponseHelper::send(['groups' => $groups]);
    }

    public static function createGroup(array $params, ?array $user): never
    {
        $data = ValidationHelper::parseJsonBody();
        ValidationHelper::requireFields($data, ['name']);

        $repo  = new GroupRepository();
        $newId = $repo->create($data);
        $group = $repo->findById($newId);

        Logger::logRequest('POST', '/groups', $data, $user, $group?->toArray(), 201, 'group', 'create');
        ResponseHelper::send(['status_code' => 0, 'group' => $group?->toArray()], 201);
    }

    public static function updateGroup(array $params, ?array $user): never
    {
        $groupId = ValidationHelper::requirePositiveInt($params['group_id'] ?? 0, 'group_id');
        $data    = ValidationHelper::parseJsonBody();

        $repo   = new GroupRepository();
        $before = $repo->findById($groupId)?->toArray();
        if ($before === null) {
            ResponseHelper::notFound('Group not found');
        }

        $repo->update($groupId, $data);
        $after = $repo->findById($groupId)?->toArray();

        Logger::logRequest('PUT', '/groups/' . $groupId, $data, $user, $after, 200, 'group', 'update', $before, $after);
        ResponseHelper::send(['status_code' => 0, 'group' => $after]);
    }

    public static function deleteGroup(array $params, ?array $user): never
    {
        $groupId = ValidationHelper::requirePositiveInt($params['group_id'] ?? 0, 'group_id');
        $repo    = new GroupRepository();
        $before  = $repo->findById($groupId)?->toArray();
        if ($before === null) {
            ResponseHelper::notFound('Group not found');
        }

        $repo->delete($groupId);

        Logger::logRequest('DELETE', '/groups/' . $groupId, [], $user, null, 200, 'group', 'delete', $before, null);
        ResponseHelper::send(['status_code' => 0, 'message' => 'Group deleted']);
    }
}
