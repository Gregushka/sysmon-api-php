<?php

class Screen
{
    public int     $id;
    public string  $type;       // screen_types.name
    public string  $name;
    public string  $description;
    public ?string $tabHeader;
    public ?string $background;
    public ?array  $settings;
    public array   $aggregates = [];   // keyed by aggregate name

    public function __construct(array $row)
    {
        $this->id          = (int)$row['id'];
        $this->type        = $row['type']        ?? $row['type_name'] ?? '';
        $this->name        = $row['name'];
        $this->description = $row['description'] ?? '';
        $this->tabHeader   = $row['tab_header']  ?? null;
        $this->background  = $row['background']  ?? null;
        $this->settings    = isset($row['settings'])
            ? json_decode($row['settings'], true)
            : null;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'type'        => $this->type,
            'name'        => $this->name,
            'description' => $this->description,
            'tab_header'  => $this->tabHeader,
            'settings'    => $this->settings ?? (object)[],
            'background'  => $this->background,
            'aggregates'  => $this->aggregates ?: [],
        ];
    }
}
