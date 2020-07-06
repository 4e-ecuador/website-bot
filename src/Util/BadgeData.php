<?php

namespace App\Util;

class BadgeData
{
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public string $code;
    public string $title;
    public string $description;
}
