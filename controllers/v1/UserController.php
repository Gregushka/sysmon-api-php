<?php

class UserController
{
    public static function getAll(array $params, ?array $user): never
    {
        $repo   = new UserRepository();
        $users  = array_map(fn($u) => $u->toArray(), $repo->findAll());

        $roleRepo  = new RoleRepository();
        $groupRepo = new GroupRepository();

        ResponseHelper::send([
            'users'  => $users,
            'groups' => array_map(fn($g) => $g->toArray(), $groupRepo->findAll()),
            'roles'  => array_map(fn($r) => $r->toArray(), $roleRepo->findAll()),
        ]);
    }

    public static function getOne(array $params, ?array $user): never
    {
        $userId = ValidationHelper::requirePositiveInt($params['user_id'] ?? 0, 'user_id');

        // Non-admin users may only retrieve their own profile
        if (!self::isAdmin($user) && (int)$user['user_id'] !== $userId) {
            ResponseHelper::forbidden();
        }

        $repo   = new UserRepository();
        $target = $repo->findById($userId);
        if ($target === null) {
            ResponseHelper::notFound('User not found');
        }

        $roleRepo  = new RoleRepository();
        $groupRepo = new GroupRepository();

        ResponseHelper::send([
            'users'  => [$target->toArray()],
            'groups' => array_map(fn($g) => $g->toArray(), $groupRepo->findAll()),
            'roles'  => array_map(fn($r) => $r->toArray(), $roleRepo->findAll()),
        ]);
    }

    public static function create(array $params, ?array $user): never
    {
        $data = ValidationHelper::parseJsonBody();
        ValidationHelper::requireFields($data, ['login', 'password']);

        // password arrives as sha256 from client; we store bcrypt of that
        $data['password'] = AuthHelper::hashPassword($data['password']);

        $repo  = new UserRepository();
        $newId = $repo->create($data);

        if (isset($data['roles'])) {
            $repo->setRoles($newId, (array)$data['roles']);
        }
        if (isset($data['groups'])) {
            $repo->setGroups($newId, (array)$data['groups']);
        }

        $newUser = $repo->findById($newId);

        Logger::logRequest('POST', '/users', ['login' => $data['login']], $user, $newUser?->toArray(), 201, 'user', 'create');
        ResponseHelper::send(['status_code' => 0, 'user' => $newUser?->toArray()], 201);
    }

    public static function update(array $params, ?array $user): never
    {
        $userId = ValidationHelper::requirePositiveInt($params['user_id'] ?? 0, 'user_id');

        if (!self::isAdmin($user) && (int)$user['user_id'] !== $userId) {
            ResponseHelper::forbidden();
        }

        $data   = ValidationHelper::parseJsonBody();
        $repo   = new UserRepository();
        $before = $repo->findById($userId)?->toArray();

        if ($before === null) {
            ResponseHelper::notFound('User not found');
        }

        if (isset($data['password'])) {
            $data['password'] = AuthHelper::hashPassword($data['password']);
        }

        $repo->update($userId, $data);
        $after = $repo->findById($userId)?->toArray();

        Logger::logRequest('PUT', '/users/' . $userId, $data, $user, $after, 200, 'user', 'update', $before, $after);
        ResponseHelper::send(['status_code' => 0, 'user' => $after]);
    }

    public static function delete(array $params, ?array $user): never
    {
        $userId = ValidationHelper::requirePositiveInt($params['user_id'] ?? 0, 'user_id');

        // Prevent self-delete
        if ((int)$user['user_id'] === $userId) {
            ResponseHelper::error(-1, 'Cannot delete your own account', 403);
        }

        $repo   = new UserRepository();
        $before = $repo->findById($userId)?->toArray();
        if ($before === null) {
            ResponseHelper::notFound('User not found');
        }

        $repo->delete($userId);

        Logger::logRequest('DELETE', '/users/' . $userId, [], $user, null, 200, 'user', 'delete', $before, null);
        ResponseHelper::send(['status_code' => 0, 'message' => 'User deleted']);
    }

    public static function assignRoles(array $params, ?array $user): never
    {
        $userId = ValidationHelper::requirePositiveInt($params['user_id'] ?? 0, 'user_id');
        $data   = ValidationHelper::parseJsonBody();
        ValidationHelper::requireFields($data, ['roles']);

        $repo = new UserRepository();
        if ($repo->findById($userId) === null) {
            ResponseHelper::notFound('User not found');
        }

        $repo->setRoles($userId, (array)$data['roles']);
        $after = $repo->findById($userId)?->toArray();

        Logger::logRequest('POST', '/users/' . $userId . '/roles', $data, $user, $after, 200, 'user', 'roles');
        ResponseHelper::send(['status_code' => 0, 'user' => $after]);
    }

    public static function assignGroups(array $params, ?array $user): never
    {
        $userId = ValidationHelper::requirePositiveInt($params['user_id'] ?? 0, 'user_id');
        $data   = ValidationHelper::parseJsonBody();
        ValidationHelper::requireFields($data, ['groups']);

        $repo = new UserRepository();
        if ($repo->findById($userId) === null) {
            ResponseHelper::notFound('User not found');
        }

        $repo->setGroups($userId, (array)$data['groups']);
        $after = $repo->findById($userId)?->toArray();

        Logger::logRequest('POST', '/users/' . $userId . '/groups', $data, $user, $after, 200, 'user', 'groups');
        ResponseHelper::send(['status_code' => 0, 'user' => $after]);
    }

    private static function isAdmin(?array $user): bool
    {
        if ($user === null) return false;
        $roleNames = array_column($user['roles'] ?? [], 'name');
        return in_array('admin', $roleNames, true) || in_array('almighty', $roleNames, true);
    }
}
