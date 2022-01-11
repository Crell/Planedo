<?php

namespace App\MessageHandler;

use App\Entity\FeedEntry;
use App\Message\ApproveEntries;
use App\Repository\FeedEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ApproveEntriesHandler implements MessageHandlerInterface
{
    private FeedEntryRepository $entryRepo;

    public function __construct(
        private EntityManagerInterface $em,
        private ?LoggerInterface $logger = null,
    ) {
        $this->logger ??= new NullLogger();

        $this->entryRepo = $this->em->getRepository(FeedEntry::class);
    }

    public function __invoke(ApproveEntries $message)
    {
        $this->entryRepo->approve(...$message->entryIds);
    }
}