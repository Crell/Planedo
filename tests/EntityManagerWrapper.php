<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Feed;
use App\Entity\FeedEntry;
use App\Entity\User;
use App\Repository\FeedEntryRepository;
use App\Repository\FeedRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait EntityManagerWrapper
{
    private EntityManagerInterface $em;

    protected function entityManager(): EntityManagerInterface
    {
        return $this->em ??= $this->getEntityManager();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        $container = self::getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        return $em;
    }

    protected function feedRepo(): FeedRepository
    {
        return $this->entityManager()->getRepository(Feed::class);
    }

    protected function feedEntryRepo(): FeedEntryRepository
    {
        return $this->entityManager()->getRepository(FeedEntry::class);
    }

    protected function userRepo(): UserRepository
    {
        return $this->entityManager()->getRepository(User::class);
    }

    abstract protected static function getContainer(): ContainerInterface;

}