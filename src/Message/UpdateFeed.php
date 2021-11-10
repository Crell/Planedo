<?php

namespace App\Message;

final class UpdateFeed
{
    public function __construct(
        public int $feedId,
    ) {}
}
