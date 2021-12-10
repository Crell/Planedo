<?php

namespace App\MessageHandler;

use App\Entity\FeedEntry;
use App\Message\PurgeOldEntries;
use App\Repository\FeedEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class PurgeOldEntriesHandler implements MessageHandlerInterface
{
    protected FeedEntryRepository $entryRepo;

    public function __construct(
        private EntityManagerInterface $em,
        protected string $purgeBefore,
        private ?LoggerInterface $logger = null,
    ) {
        $this->logger ??= new NullLogger();

        $this->entryRepo = $this->em->getRepository(FeedEntry::class);
    }

    public function __invoke(PurgeOldEntries $message)
    {
        $deleteBefore = new \DateTimeImmutable($this->purgeBefore);

        try {
            $this->entryRepo->deleteOlderThan($deleteBefore);
        } catch (\Exception $e) {
            $this->logger->error('Failed purging old entries: {message}', [
                'exception' => $e,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
