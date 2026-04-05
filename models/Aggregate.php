<?php

class Aggregate
{
    public int     $id;
    public string  $name;
    public ?string $description;
    public ?array  $settings;
    public array   $indicators = [];

    public function __construct(array $row)
    {
        $this->id          = (int)$row['id'];
        $this->name        = $row['name'];
        $this->description = $row['description'] ?? null;
        $this->settings    = isset($row['settings'])
            ? json_decode($row['settings'], true)
            : null;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'indicators'  => $this->indicators,
        ];
    }
}
