<?php

namespace App\Type;

class Emoji
{
    public function __construct(
        private readonly string $name,
        private readonly string $description,
        private readonly string $unicode,
        private readonly string $bytecode,
        private readonly string $native = 'x'
    ) {
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
