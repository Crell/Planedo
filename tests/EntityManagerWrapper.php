<?php

declare(strict_types=1);

namespace App\Tests;

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

    abstract protected static function getContainer(): ContainerInterface;

}