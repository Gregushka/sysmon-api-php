<?php

/**
 * ControlController
 *
 * Lists controls available to the front end.
 * Command handling logic (Switch_NC, Switch_NO, etc.)
 * is a stub — to be implemented in a later iteration.
 */
class ControlController
{
    public static function getAll(array $params, ?array $user): never
    {
        $repo     = new ControlRepository();
        $controls = array_map(fn($c) => $c->toArray(), $repo->findAll());
        ResponseHelper::send(['controls' => $controls]);
    }
}
