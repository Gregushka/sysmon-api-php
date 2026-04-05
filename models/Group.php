<?php

class Group
{
    public int     $id;
    public string  $name;
    public ?string $description;
    public ?array  $permissions;
    public array   $screens    = [];
    public array   $aggregates = [];

    public function __construct(array $row)
    {
        $this->id          = (int)$row['id'];
        $this->name        = $row['name'];
        $this->description = $row['description'] ?? null;
        $this->permissions = isset($row['permissions'])
            ? json_decode($row['permissions'], true)
            : null;
    }

    public function toArray(): array
    {
        $out = [
            'id'         => $this->id,
            'group_name' => $this->name,
        ];
        if (!empty($this->aggregates)) {
            $out['aggregates'] = $this->aggregates;
        }
        if (!empty($this->screens)) {
            $out['screens'] = $this->screens;
        }
        return $out;
    }
}
