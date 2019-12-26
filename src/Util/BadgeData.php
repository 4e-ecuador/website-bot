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

    public $code;
    public $title;
    public $description;
}
