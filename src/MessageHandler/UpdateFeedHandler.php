<?php

namespace App\MessageHandler;

use App\Entity\Feed;
use App\Entity\FeedEntry;
use App\FeedReader;
use App\Message\UpdateFeed;
use App\Repository\FeedEntryRepository;
use App\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Feed\Reader\Collection\Author;
use Laminas\Feed\Reader\Entry\EntryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateFeedHandler implements MessageHandlerInterface
{
    private FeedRepository $feedRepo;
    private FeedEntryRepository $entryRepo;

    public function __construct(
        private EntityManagerInterface $em,
        private FeedReader $reader,
        private ?LoggerInterface $logger = null,
    ) {
        $this->logger ??= new NullLogger();

        $this->feedRepo = $this->em->getRepository(Feed::class);
        $this->entryRepo = $this->em->getRepository(FeedEntry::class);
    }

    public function __invoke(UpdateFeed $message): void
    {
        $feed = $this->feedRepo->find($message->feedId);

        if (is_null($feed)) {
            $this->logger->warning('Tried to fetch feed for {id}, but no feed was found.', ['id' => $message->feedId]);
            return;
        }

        try {
            $feedData = $this->reader->import($feed?->getFeedLink());
        } catch (\Laminas\Feed\Reader\Exception\RuntimeException $e) {
            $this->logger->error('Exception caught importing feed {name}', [
                'name' => $feed->getTitle(),
                'exception' => $e,
            ]);
            return;
        }

        $this->em->wrapInTransaction(function (EntityManagerInterface $em) use ($feed, $feedData) {
            $optional = [
                'Link',
                // getFeedLink() is broken and buggy, so don't use it
                // cf: https://github.com/laminas/laminas-feed/issues/44
                //'FeedLink',
                'Copyright',
                'DateCreated',
                'DateModified',
                'Generator',
                'Language',
            ];

            // Update the feed itself with data from the feed.
            foreach ($optional as $method) {
                $val = $feedData->{'get' . $method}();
                if ($val) {
                    $feed->{'set' . $method}($val);
                }
            }

            // Authors are an over-engineered array, which has a singular name
            // even though it's an iterable, even though getAuthors() is typed
            // to return an array. Bad design in Laminas Feed. That's why we can't
            // easily just use a map operation.
            /** @var Author $authors */
            $authors = $feedData->getAuthors() ?? [];
            $authorNames = [];
            foreach ($authors as $a) {
                $authorNames[] = $a['name'];
            }
            $feed->setAuthors($authorNames);

            // Doctrine... seemingly can't handle delete-and-recreate as a way to handle
            // dependent objects, AND there's no O(1) way to find a particular related object
            // by ID.  So we have to build our own.  Ideally someone who knows Doctrine better
            // than I do will figure out a cleaner way to do this.

            /** @var FeedEntry[] $existing */
            $existing = $feed->getEntries()->getValues();
            $lookup = [];
            foreach ($existing as $current) {
                $lookup[$current->getLink()] = $current;
            }

            /** @var EntryInterface $item */
            foreach ($feedData as $item) {
                $entry = $lookup[$item->getLink()] ?? new FeedEntry();

                /** @var Author $authors */
                $authors = $feedData->getAuthors() ?? [];
                $authorNames = [];
                foreach ($authors as $a) {
                    $authorNames[] = $a['name'];
                }
                $feed->setAuthors($authorNames);

                $entry
                    ->setFeed($feed)
                    ->setTitle($item->getTitle())
                    ->setLink($item->getLink())
                    ->setDescription($item->getDescription() ?? '')
                    ->setDateModified($item->getDateModified() ? \DateTimeImmutable::createFromInterface($item->getDateModified()) : null)
                    ->setDateCreated($item->getDateCreated() ? \DateTimeImmutable::createFromInterface($item->getDateCreated()) : null)
                    ->setAuthors($authorNames)
                ;
                $em->persist($entry);
                $feed->addEntry($entry);
            }

            // Mark that it has been updated.
            $feed->setLastUpdated(new \DateTimeImmutable(timezone: new \DateTimeZone('UTC')));

            $em->persist($feed);
            $em->flush();
        });
    }
}
