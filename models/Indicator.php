<?php

class Indicator
{
    public int     $id;
    public string  $indId;
    public ?string $dataId;
    public string  $type;        // indicator_types.front_name
    public ?string $label;
    public ?string $unit;        // units.symbol
    public ?int    $top;
    public ?int    $left;
    public ?array  $labelFont;
    public ?array  $unitFont;
    public ?array  $valueFont;
    public ?int    $radius;
    public ?int    $size;
    public ?array  $box;
    public ?array  $settings;
    public mixed   $value = null;  // runtime value from mock / data source

    public function __construct(array $row)
    {
        $this->id        = (int)$row['id'];
        $this->indId     = $row['ind_id'];
        $this->dataId    = $row['data_id']  ?? null;
        $this->type      = $row['type']     ?? $row['front_name'] ?? '';
        $this->label     = $row['label']    ?? null;
        $this->unit      = $row['unit']     ?? null;   // already resolved to symbol
        $this->top       = isset($row['top'])    ? (int)$row['top']    : null;
        $this->left      = isset($row['left'])   ? (int)$row['left']   : null;
        $this->labelFont = isset($row['label_font'])  ? json_decode($row['label_font'],  true) : null;
        $this->unitFont  = isset($row['unit_font'])   ? json_decode($row['unit_font'],   true) : null;
        $this->valueFont = isset($row['value_font'])  ? json_decode($row['value_font'],  true) : null;
        $this->radius    = isset($row['radius']) ? (int)$row['radius'] : null;
        $this->size      = isset($row['size'])   ? (int)$row['size']   : null;
        $this->box       = isset($row['box'])      ? json_decode($row['box'],      true) : null;
        $this->settings  = isset($row['settings']) ? json_decode($row['settings'], true) : null;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'ind_id'      => $this->indId,
            'data_id'     => $this->dataId,
            'type'        => $this->type,
            'label'       => $this->label,
            'unit'        => $this->unit,
            'top'         => $this->top,
            'left'        => $this->left,
            'label_font'  => $this->labelFont,
            'unit_font'   => $this->unitFont,
            'value_font'  => $this->valueFont,
            'radius'      => $this->radius,
            'size'        => $this->size,
            'box'         => $this->box,
            'settings'    => $this->settings,
            'value'       => $this->value,
        ];
    }
}
