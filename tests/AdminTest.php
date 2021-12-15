<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminTest extends WebTestCase
{
    use SetupUtils;

    /**
     * @test
     */
    public function feed_index_loads(): void
    {
        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $crawler = $client->request('GET', '/admin');
        $client->followRedirect();

        $client->clickLink('Feeds');

        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function feed_entry_index_loads(): void
    {
        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $crawler = $client->request('GET', '/admin');
        $client->followRedirect();

        $client->clickLink('Feed Entries');

        self::assertResponseIsSuccessful();
    }
}
