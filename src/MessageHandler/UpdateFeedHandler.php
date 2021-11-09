<?php

namespace App\MessageHandler;

use App\Entity\Feed;
use App\Entity\FeedEntry;
use App\GuzzleClient;
use App\Message\UpdateFeed;
use App\Repository\FeedEntryRepository;
use App\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Feed\Reader\Reader;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateFeedHandler implements MessageHandlerInterface
{
    private FeedRepository $feedRepo;
    private FeedEntryRepository $entryRepo;

    public function __construct(
        private EntityManagerInterface $em,
        private ?LoggerInterface $logger = null,
    ) {
        $this->logger ??= new NullLogger();

        $this->feedRepo = $this->em->getRepository(Feed::class);
        $this->entryRepo = $this->em->getRepository(FeedEntry::class);
    }

    public function __invoke(UpdateFeed $message): void
    {
        /** @var FeedRepository $repo */
//        $feedRepo = $this->em->getRepository(Feed::class);
//        $entryRepo = $this->em->getRepository(FeedEntryRepository::class);

        $feed = $this->feedRepo->find($message->feedId);

        if (is_null($feed)) {
            $this->logger->warning('Tried to fetch feed for {id}, but no feed was found.', ['id' => $message->feedId]);
            return;
        }

        try {
            Reader::setHttpClient(new GuzzleClient());
            $feedData = Reader::import($feed?->getLink());
        } catch (\Laminas\Feed\Reader\Exception\RuntimeException $e) {
            $this->logger->error('Exception caught importing feed {name}', [
                'name' => $feed->getTitle(),
                'exception' => $e,
            ]);
        }

        $this->em->wrapInTransaction(function (EntityManagerInterface $em) use ($feed, $feedData) {
            // Remove all existing entries.
            $feed->getEntries()->clear();

            // Insert the new ones.

            foreach ($feedData as $item) {
                $entry = new FeedEntry();
                $entry
                    ->setFeed($feed)
                    ->setTitle($item->getTitle())
                    ->setLink($item->getLink())
                    ->setSummary($item->getDescription())
                    // @todo Pretty sure this needs to be redesigned.
                    ->setAuthorName($item->getAuthor(0)['name'])
                ;
                $em->persist($entry);
                $feed->addEntry($entry);
            }

            $em->persist($feed);
            $em->flush();
        });
    }
}
