<?php

namespace App\MessageHandler;

use App\Entity\FeedEntry;
use App\Message\ApproveEntry;
use App\Repository\FeedEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ApproveEntryHandler implements MessageHandlerInterface
{
    private FeedEntryRepository $entryRepo;

    public function __construct(
        private EntityManagerInterface $em,
        private ?LoggerInterface $logger = null,
    ) {
        $this->logger ??= new NullLogger();

        $this->entryRepo = $this->em->getRepository(FeedEntry::class);
    }

    public function __invoke(ApproveEntry $message)
    {
        $entry = $this->entryRepo->find($message->entryId);
        if (!$entry) {
            $this->logger->warning('Tried to restore entry {id}, but it was not found.', [
                'id' => $message->entryId,
            ]);
        }

        $entry->setApproved(true);

        $this->em->persist($entry);
        $this->em->flush();
    }
}
