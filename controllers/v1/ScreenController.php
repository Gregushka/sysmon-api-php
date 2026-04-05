<?php

class ScreenController
{
    public static function getAll(array $params, ?array $user): never
    {
        $mockValues = DataController::getMockValues();
        $repo       = new ScreenRepository();
        $userId     = (int)$user['user_id'];

        // almighty / admin see all screens; others see only their permitted screens
        $screens    = $repo->findAll(self::isAdmin($user) ? null : $userId);
        $screensOut = [];
        foreach ($screens as $screen) {
            $screen->aggregates = $repo->buildAggregatesForScreen($screen->id, $mockValues);
            $screensOut[]       = $screen->toArray();
        }

        ResponseHelper::send([
            'data' => [
                'hdr'     => AppHeader::get('force_update'),
                'screens' => $screensOut,
            ],
        ]);
    }

    public static function getOne(array $params, ?array $user): never
    {
        $screenId = ValidationHelper::requirePositiveInt($params['screen_id'] ?? 0, 'screen_id');

        $mockValues = DataController::getMockValues();
        $repo       = new ScreenRepository();
        $userId     = (int)$user['user_id'];

        $screen = $repo->findById($screenId, self::isAdmin($user) ? null : $userId);
        if ($screen === null) {
            ResponseHelper::notFound('Screen not found or access denied');
        }

        $screen->aggregates = $repo->buildAggregatesForScreen($screen->id, $mockValues);

        ResponseHelper::send([
            'data' => [
                'hdr'     => AppHeader::get('force_update'),
                'screens' => [$screen->toArray()],
            ],
        ]);
    }

    public static function create(array $params, ?array $user): never
    {
        $data = ValidationHelper::parseJsonBody();
        ValidationHelper::requireFields($data, ['type_id', 'name']);

        $repo     = new ScreenRepository();
        $newId    = $repo->create($data);
        $screen   = $repo->findById($newId);

        Logger::logRequest('POST', '/screen', $data, $user, $screen?->toArray(), 201, 'screen', 'create');
        ResponseHelper::send(['status_code' => 0, 'screen' => $screen?->toArray()], 201);
    }

    public static function update(array $params, ?array $user): never
    {
        $screenId = ValidationHelper::requirePositiveInt($params['screen_id'] ?? 0, 'screen_id');
        $data     = ValidationHelper::parseJsonBody();

        $repo   = new ScreenRepository();
        $before = $repo->findById($screenId)?->toArray();

        if ($before === null) {
            ResponseHelper::notFound('Screen not found');
        }

        $repo->update($screenId, $data);
        $after = $repo->findById($screenId)?->toArray();

        Logger::logRequest('PUT', '/screen/' . $screenId, $data, $user, $after, 200, 'screen', 'update', $before, $after);
        ResponseHelper::send(['status_code' => 0, 'screen' => $after]);
    }

    public static function delete(array $params, ?array $user): never
    {
        $screenId = ValidationHelper::requirePositiveInt($params['screen_id'] ?? 0, 'screen_id');

        $repo   = new ScreenRepository();
        $before = $repo->findById($screenId)?->toArray();

        if ($before === null) {
            ResponseHelper::notFound('Screen not found');
        }

        $repo->delete($screenId);

        Logger::logRequest('DELETE', '/screen/' . $screenId, [], $user, null, 200, 'screen', 'delete', $before, null);
        ResponseHelper::send(['status_code' => 0, 'message' => 'Screen deleted']);
    }

    private static function isAdmin(?array $user): bool
    {
        if ($user === null) return false;
        $roleNames = array_column($user['roles'] ?? [], 'name');
        return in_array('admin', $roleNames, true) || in_array('almighty', $roleNames, true);
    }
}
