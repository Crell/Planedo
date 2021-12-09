<?php

namespace App\Tests;

use App\Entity\FeedEntry;
use App\Repository\FeedEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HtmlFeedTest extends WebTestCase
{
    use SetupUtils;

    /**
     * @test
     */
    public function main_feed_has_data(): void
    {
        $client = static::createClient();

        $this->mockFeedClient();
        $this->populateFeeds();

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();

        $container = self::getContainer();

        // Confirm the number of articles on the first page.
        $articles = $crawler->filter('article');
        self::assertCount($container->getParameter('app.feeds.items-per-page'), $articles);

        // Confirm there is next link but no prev link, since it's the front page.
        $next = $crawler->filter('a[rel="next"]');
        self::assertCount(1, $next);
        $prev = $crawler->filter('a[rel="prev"]');
        self::assertCount(0, $prev);

        $this->assertRawEntryCount();
    }

    /**
     * @test
     */
    public function rejected_entries_dont_show(): void
    {
        $entryToExclude = 'https://www.example.com/blog/b';

        $excludedContent = 'Description B';

        $client = static::createClient();

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

        $crawler = $client->request('GET', '/');
        self::assertResponseIsSuccessful();

        // Confirm that the rejected entry is not here.
        // @todo I'm pretty sure this is a stupid way of checking this.
        $response = $client->getResponse();
        self::assertStringNotContainsString($excludedContent, $response->getContent());
        $link = $crawler->filter(sprintf('a[href="%s"]', $entryToExclude));
        self::assertCount(0, $link);
    }

}
