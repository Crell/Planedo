<?php

namespace App\DataFixtures;

use App\Entity\Feed;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FeedFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $feed = new Feed();
        $feed
            ->setTitle('Garfieldtech')
            ->setFeedLink('https://www.garfieldtech.com/blog/feed');
        $manager->persist($feed);

        $feed = new Feed();
        $feed
            ->setTitle('Planet PHP RSS')
            ->setFeedLink('http://www.planet-php.org/rss/');
        $manager->persist($feed);

        $feed = new Feed();
        $feed
            ->setTitle('Fake Feed')
            ->setFeedLink('http://www.example.com/');
        $manager->persist($feed);

        $manager->flush();
    }
}
