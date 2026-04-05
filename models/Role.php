<?php

class Role
{
    public int     $id;
    public string  $name;
    public ?string $description;
    public ?array  $permissions;

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
        return [
            'id'               => $this->id,
            'role_name'        => $this->name,
            'role_description' => $this->description,
            'permissions'      => $this->permissions,
        ];
    }
}
