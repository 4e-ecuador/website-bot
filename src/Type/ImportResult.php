<?php

namespace App\Type;

class ImportResult
{
    public ?array $currents = [];
    public ?array $diff = [];
    public ?array $medalUps = [];
    public ?array $medalDoubles = [];
    public ?int $newLevel = 0;
    public ?int $recursions = 0;
}
