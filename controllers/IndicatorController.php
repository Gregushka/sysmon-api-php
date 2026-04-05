<?php

class IndicatorController
{
    /**
     * GET /indicators[/{screen_id}[/{ind_id}]]
     */
    public static function getValues(array $params, ?array $user): never
    {
        $screenId = isset($params['screen_id']) && $params['screen_id'] !== ''
            ? ValidationHelper::requirePositiveInt($params['screen_id'], 'screen_id')
            : null;

        $indId = isset($params['ind_id']) && $params['ind_id'] !== ''
            ? $params['ind_id']
            : null;

        // screen_id is required when ind_id is specified
        if ($indId !== null && $screenId === null) {
            ResponseHelper::error(-1, 'screen_id is required when ind_id is specified', 422);
        }

        $repo       = new IndicatorRepository();
        $mockValues = DataController::getMockValues();
        $rows       = $repo->findByContext($screenId, $indId);

        $indicators = [];
        foreach ($rows as $row) {
            $indicators[] = [
                'ind_id'    => $row['ind_id'],
                'value'     => $mockValues[$row['ind_id']] ?? null,
                'screen'    => $row['screen_name'],
                'aggregate' => $row['aggregate_name'],
            ];
        }

        ResponseHelper::send([
            'data' => [
                'hdr'        => AppHeader::get('force_update'),
                'indicators' => $indicators,
            ],
        ]);
    }

    /**
     * POST /position/{ind_id}
     * Body: [{"ind_id":"pe1","screen":"Pumps","aggregate":"aggr_1","top":564,"left":1050}]
     * Note: ind_id alone is ambiguous; the screen name is used to resolve the correct indicator.
     */
    public static function setPosition(array $params, ?array $user): never
    {
        $indIdParam = $params['ind_id'] ?? '';
        $body       = ValidationHelper::parseJsonBody();

        // Accept both array-of-one and plain object
        $data = isset($body[0]) ? $body[0] : $body;

        ValidationHelper::requireFields($data, ['ind_id', 'screen', 'top', 'left']);

        $indId    = (string)$data['ind_id'];
        $screenName = (string)$data['screen'];
        $top      = (int)$data['top'];
        $left     = (int)$data['left'];

        // Resolve screen_id by name
        $db       = Database::get();
        $scrStmt  = $db->prepare('SELECT id FROM screens WHERE name = :name LIMIT 1');
        $scrStmt->execute([':name' => $screenName]);
        $scrRow   = $scrStmt->fetch();

        if (!$scrRow) {
            ResponseHelper::notFound("Screen '{$screenName}' not found");
        }

        $repo    = new IndicatorRepository();
        $indRow  = $repo->findIndicatorInScreen($indId, (int)$scrRow['id']);

        if (!$indRow) {
            ResponseHelper::notFound("Indicator '{$indId}' not found in screen '{$screenName}'");
        }

        $before = ['top' => $indRow['top'], 'left' => $indRow['left']];
        $repo->updatePosition((int)$indRow['id'], $top, $left);

        $response = [
            'status_code' => 0,
            'status_text' => 'New position set',
            'ind_id'      => $indId,
            'screen'      => $screenName,
            'aggregate'   => $data['aggregate'] ?? $indRow['aggregate_name'],
            'top'         => $top,
            'left'        => $left,
        ];

        Logger::logRequest(
            'POST', '/position/' . $indId, $data, $user, $response, 200,
            'indicator', 'position',
            $before, ['top' => $top, 'left' => $left]
        );

        ResponseHelper::send($response);
    }
}
