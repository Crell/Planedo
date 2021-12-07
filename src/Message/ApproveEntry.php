<?php

namespace App\Message;

final class ApproveEntry
{
    public function __construct(
        public string $entryId,
    ) {}
}
