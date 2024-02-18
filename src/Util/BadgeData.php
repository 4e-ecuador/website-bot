<?php

namespace App\Util;

class BadgeData
{
    /**
     * @param array<string, string> $data
     */
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
