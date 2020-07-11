<?php

namespace App\Type;

class Emoji
{
    private string $name;
    private string $description;
    private string $unicode;
    private string $bytecode;

    public function __construct(string $name, string $description, string $unicode, string $bytecode)
    {
        $this->name = $name;
        $this->description = $description;
        $this->unicode = $unicode;
        $this->bytecode = $bytecode;
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
}
