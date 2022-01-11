<?php

namespace App\Message;

final class RejectEntries
{
    /** @var string[]  */
    public array $entryIds;

    public function __construct(
        string ...$entryIds,
    ) {
        $this->entryIds = $entryIds;
    }
}
