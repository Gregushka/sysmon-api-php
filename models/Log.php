<?php

class Log
{
    public int     $id;
    public string  $timestamp;
    public int     $errorLevel;
    public ?string $callerIp;
    public ?string $command;
    public ?string $method;
    public ?array  $commandParameters;
    public ?array  $response;
    public ?string $dataObject;
    public ?string $objectAttribute;
    public ?array  $dataWas;
    public ?array  $dataIs;
    public ?int    $httpStatusCode;

    public function __construct(array $row)
    {
        $this->id                = (int)$row['id'];
        $this->timestamp         = $row['timestamp'];
        $this->errorLevel        = (int)$row['error_level'];
        $this->callerIp          = $row['caller_ip']         ?? null;
        $this->command           = $row['command']           ?? null;
        $this->method            = $row['method']            ?? null;
        $this->commandParameters = isset($row['command_parameters'])
            ? json_decode($row['command_parameters'], true) : null;
        $this->response          = isset($row['response'])
            ? json_decode($row['response'], true) : null;
        $this->dataObject        = $row['data_object']       ?? null;
        $this->objectAttribute   = $row['object_attribute']  ?? null;
        $this->dataWas           = isset($row['data_was'])
            ? json_decode($row['data_was'], true) : null;
        $this->dataIs            = isset($row['data_is'])
            ? json_decode($row['data_is'], true) : null;
        $this->httpStatusCode    = isset($row['http_status_code'])
            ? (int)$row['http_status_code'] : null;
    }

    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'timestamp'          => $this->timestamp,
            'error_level'        => $this->errorLevel,
            'caller_ip'          => $this->callerIp,
            'command'            => $this->command,
            'method'             => $this->method,
            'command_parameters' => $this->commandParameters,
            'response'           => $this->response,
            'data_object'        => $this->dataObject,
            'object_attribute'   => $this->objectAttribute,
            'data_was'           => $this->dataWas,
            'data_is'            => $this->dataIs,
            'http_status_code'   => $this->httpStatusCode,
        ];
    }
}
