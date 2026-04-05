<?php

class User
{
    public int     $id;
    public string  $login;
    public ?string $fname;
    public ?string $lname;
    public ?string $pname;
    public ?string $position;
    public array   $roles  = [];
    public array   $groups = [];

    public function __construct(array $row)
    {
        $this->id       = (int)$row['id'];
        $this->login    = $row['login'];
        $this->fname    = $row['fname']    ?? null;
        $this->lname    = $row['lname']    ?? null;
        $this->pname    = $row['pname']    ?? null;
        $this->position = $row['position'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'login'        => $this->login,
            'roles'        => $this->roles,
            'user_groups'  => $this->groups,
            'fname'        => $this->fname,
            'lname'        => $this->lname,
            'pname'        => $this->pname,
            'position'     => $this->position,
        ];
    }
}
