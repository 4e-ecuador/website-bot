<?php

namespace App\Type;

class FsAttendeesInfo
{
    public function __construct(
        /**
         * @var array<int, string> $poc
         */
        public array $poc = [],

        /**
         * @var array<int, string> $attendees
         */
        public array $attendees = [],
    ) {
    }
}
