<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Feed;
use App\Entity\FeedEntry;
use App\Message\UpdateFeed;
use App\Repository\FeedEntryRepository;
use App\Tests\Mocks\MockFeedReaderHttpClient;
use App\Tests\Mocks\SettableClock;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Feed\Reader\Http\ClientInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\MessageBusInterface;

trait SetupUtils
{
    protected function assertRawEntryCount(int $expected): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var FeedEntryRepository $entryRepo */
        $entryRepo = $em->getRepository(FeedEntry::class);
        $entries = $entryRepo->findAll();
        self::assertCount($expected, $entries);
    }

    protected function mockFeedClient(): void
    {
        $container = self::getContainer();

        $mockClient = new MockFeedReaderHttpClient([
            'https://www.garfieldtech.com/blog/feed' => 'tests/feed-data/garfieldtech.rss',
            'http://www.planet-php.org/rss/' => 'tests/feed-data/planetphp.092.rss',
            'http://www.planet-php.org/rdf/' => 'tests/feed-data/planetphp.10.xml',
            'https://www.php.net/feed.atom' => 'tests/feed-data/phpnet.atom',
            'http://www.example.com/' => 'tests/feed-data/fake1.rss',
        ]);

        $container->set(ClientInterface::class, $mockClient);
    }

    protected function mockClock(\DateTimeImmutable $time): SettableClock
    {
        $container = self::getContainer();
        $clock = new SettableClock($time);
        $container->set(ClockInterface::class, $clock);
        return $clock;
    }

    protected function populateFeeds(): void
    {
        $container = self::getContainer();

        /** @var MessageBusInterface $bus */
        $bus = $container->get(MessageBusInterface::class);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var Feed[] $feeds */
        $feeds = $em->getRepository(Feed::class)->findAll();
        self::assertCount(3, $feeds);
        foreach ($feeds as $feed) {
            $bus->dispatch(new UpdateFeed($feed->getId()));
        }
    }
}
