<?php

namespace App\Type;

class FsAttendeesInfo
{
    public function __construct(
        /**
         * @var array<string, string> $poc
         */
        public array $poc = [],

        /**
         * @var array<string, array<int, string>> $attendees
         */
        public array $attendees = [],
    ) {
    }
}
