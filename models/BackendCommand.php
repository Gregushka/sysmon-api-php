<?php

class BackendCommand
{
    public int     $id;
    public string  $command;
    public ?string $response;
    public ?string $description;

    public function __construct(array $row)
    {
        $this->id          = (int)$row['id'];
        $this->command     = $row['command'];
        $this->response    = $row['response']    ?? null;
        $this->description = $row['description'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'command'     => $this->command,
            'response'    => $this->response,
            'description' => $this->description,
        ];
    }
}
