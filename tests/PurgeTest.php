<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\FeedEntry;
use App\Message\PurgeOldEntries;
use App\Repository\FeedEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class PurgeTest extends KernelTestCase
{
    /**
     * @test
     */
    public function old_entries_get_purged(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        /** @var MessageBusInterface $bus */
        $bus = $container->get(MessageBusInterface::class);

        $bus->dispatch(new PurgeOldEntries(1));

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var FeedEntryRepository $entryRepo */
        $entryRepo = $em->getRepository(FeedEntry::class);
        $entries = $entryRepo->findAll();

        self::assertCount(0, $entries);
    }
}