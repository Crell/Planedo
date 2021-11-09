<?php

namespace App\MessageHandler;

use App\Message\UpdateFeed;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateFeedHandler implements MessageHandlerInterface
{
    public function __invoke(UpdateFeed $message)
    {
        // do something with your message
    }
}
