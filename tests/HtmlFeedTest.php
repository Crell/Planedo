<?php

namespace App\Tests;

use App\Repository\FeedEntryRepository;
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

        // Confirm the number of articles on the first page.
        $articles = $crawler->filter('article');
        self::assertCount(FeedEntryRepository::ItemsPerPage, $articles);

        // Confirm there is next link but no prev link, since it's the front page.
        $next = $crawler->filter('a[rel="next"]');
        self::assertCount(1, $next);
        $prev = $crawler->filter('a[rel="prev"]');
        self::assertCount(0, $prev);

        $this->assertRawEntryCount();
    }

}
