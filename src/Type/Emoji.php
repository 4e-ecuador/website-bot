<?php

namespace App\Type;

class Emoji
{
    private string $name;
    private string $description;
    private string $unicode;
    private string $bytecode;
    private string $native;

    public function __construct(string $name, string $description, string $unicode, string $bytecode, string $native = 'x')
    {
        $this->name = $name;
        $this->description = $description;
        $this->unicode = $unicode;
        $this->bytecode = $bytecode;
        $this->native = $native;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUnicode(): string
    {
        return $this->unicode;
    }

    public function getBytecode(): string
    {
        return $this->bytecode;
    }

    public function getNative(): string
    {
        return $this->native;
    }
}
