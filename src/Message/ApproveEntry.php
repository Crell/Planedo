<?php

namespace App\Message;

final class RestoreEntry
{
    public function __construct(
        public string $entryId,
    ) {}
}
