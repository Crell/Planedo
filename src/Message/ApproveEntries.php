<?php

namespace App\Message;

final class ApproveEntries
{
    /** @var string[]  */
    public array $entryIds;

    public function __construct(
        string ...$entryIds,
    ) {
        $this->entryIds = $entryIds;
    }
}
