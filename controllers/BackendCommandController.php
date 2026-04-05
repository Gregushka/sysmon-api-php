<?php

/**
 * BackendCommandController
 *
 * Lists backend commands available to the front end.
 * Business logic for each command (force_update, front_version, etc.)
 * is a stub — to be implemented in a later iteration.
 */
class BackendCommandController
{
    public static function getAll(array $params, ?array $user): never
    {
        $repo     = new BackendCommandRepository();
        $commands = array_map(fn($c) => $c->toArray(), $repo->findAll());
        ResponseHelper::send(['commands' => $commands]);
    }
}
