<?php

class Control
{
    public int     $id;
    public string  $name;
    public ?string $frontName;
    public ?string $typeName;   // control_types.name
    public ?string $description;

    public function __construct(array $row)
    {
        $this->id          = (int)$row['id'];
        $this->name        = $row['name'];
        $this->frontName   = $row['front_name']  ?? null;
        $this->typeName    = $row['type_name']   ?? null;
        $this->description = $row['description'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'front_name'  => $this->frontName,
            'type'        => $this->typeName,
            'description' => $this->description,
        ];
    }
}
