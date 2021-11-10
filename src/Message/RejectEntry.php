<?php

namespace App\Message;

final class RejectEntry
{
    public function __construct(
        public string $entryId,
    ) {}
}
