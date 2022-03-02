<?php

namespace App\Type;

class ImportResult
{
    /**
     * @var array<string, int>|null
     */
    public ?array $currents = [];

    /**
     * @var array<string, int>|null
     */
    public ?array $diff = [];

    /**
     * @var array<string, int>|null
     */
    public ?array $medalUps = [];

    /**
     * @var array<string, int>|null
     */
    public ?array $medalDoubles = [];

    public ?int $newLevel = 0;
    public ?int $recursions = 0;
}
