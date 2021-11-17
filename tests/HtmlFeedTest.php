<?php

namespace App\Tests;

use App\Entity\Feed;
use App\Entity\FeedEntry;
use App\Message\UpdateFeed;
use App\Repository\FeedEntryRepository;
use App\Repository\FeedRepository;
use App\Tests\Mocks\MockFeedReaderHttpClient;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Feed\Reader\Http\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class HtmlFeedTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();

        $container = self::getContainer();

        $this->mockFeedClient();
        $this->populateFeeds();

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
//        self::assertSelectorTextContains('h1', 'Hello World');

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var FeedEntryRepository $entryRepo */
        $entryRepo = $em->getRepository(FeedEntry::class);
        $entries = $entryRepo->findAll();
        self::assertCount(20, $entries);
    }

    protected function mockFeedClient(): void
    {
        $container = self::getContainer();

        $mockClient = new MockFeedReaderHttpClient([
            'https://www.garfieldtech.com/blog/feed' => 'tests/feed-data/garfieldtech.rss',
            'http://www.planet-php.org/rss/' => 'tests/feed-data/planetphp.092.rss',
            'http://www.planet-php.org/rdf/' => 'tests/feed-data/planetphp.10.xml',
        ]);

        $container->set(ClientInterface::class, $mockClient);
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
        self::assertCount(2, $feeds);
        foreach ($feeds as $feed) {
            $bus->dispatch(new UpdateFeed($feed->getId()));
        }
    }
}
