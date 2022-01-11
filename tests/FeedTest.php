<?php

namespace App\Tests;

use App\Entity\Feed;
use App\Entity\FeedEntry;
use App\Message\UpdateFeed;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Feed\Reader\Reader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeedTest extends WebTestCase
{
    use SetupUtils;

    public function feedTypeProvider(): iterable
    {
        yield 'Atom' => [
            'path' => '/atom',
            'contentType' => 'application/atom+xml',
        ];
        yield 'Rss' => [
            'path' => '/rss',
            'contentType' => 'application/rss+xml',
        ];
    }

    /**
     * @test
     * @dataProvider feedTypeProvider()
     */
    public function feed_has_data(string $path, string $contentType): void
    {
        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('2021-11-15'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $crawler = $client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', $contentType);

        $feed = Reader::importString($client->getResponse()->getContent());

        // Confirm the number of articles in the first page of the feed.
        $container = self::getContainer();
        self::assertCount($container->getParameter('app.feeds.items-per-page'), $feed);

        // Only 11 items would have survived the old-data filter when adding.
        $this->assertRawEntryCount(11);
    }

    /**
     * @test
     * @dataProvider feedTypeProvider()
     */
    public function rejected_entries_dont_show(string $path, string $contentType): void
    {
        $entryToExclude = 'https://www.example.com/blog/b';

        $excludedContent = 'Description B';

        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var FeedEntry $entry */
        $entry = $em->getRepository(FeedEntry::class)->find($entryToExclude);
        $entry->setApproved(false);
        $em->persist($entry);
        $em->flush();

        $crawler = $client->request('GET', $path);
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', $contentType);

        // Confirm the rejected entry is not in the feed.
        $feed = Reader::importString($client->getResponse()->getContent());
        foreach ($feed as $entry) {
            self::assertNotEquals($entryToExclude, $entry->getId(), 'Rejected entry found in feed.');
        }
    }

    /**
     * @test
     * @dataProvider feedTypeProvider()
     */
    public function inactive_feeds_dont_show(string $path, string $contentType): void
    {
        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $container = self::getContainer();

        // Disable one feed, even though its data has been fetched.

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var Feed[] $feeds */
        $feeds = $em->getRepository(Feed::class)->findAll();
        foreach ($feeds as $f) {
            if ($f->getFeedLink() === 'https://www.garfieldtech.com/blog/feed') {
                $f->setActive(false);
                $em->persist($f);
            }
        }
        $em->flush();

        $crawler = $client->request('GET', $path);
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', $contentType);

        // Confirm that the disabled feed doesn't show.
        $feed = Reader::importString($client->getResponse()->getContent());
        foreach ($feed as $entry) {
            self::assertNotEquals('garfieldtech', $entry->getPermalink());
        }
    }
}
