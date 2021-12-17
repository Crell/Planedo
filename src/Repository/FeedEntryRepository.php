<?php

namespace App\Repository;

use App\Entity\FeedEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FeedEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedEntry[]    findAll()
 * @method FeedEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, protected int $itemsPerPage)
    {
        parent::__construct($registry, FeedEntry::class);
    }

    public function latestEntriesPaginator(int $offset): Paginator
    {
        // Filter out unapproved entries and entries of a feed that is disabled.
        $query = $this->getEntityManager()
            ->createQuery('SELECT e FROM App\\Entity\\FeedEntry e 
                JOIN e.feed f 
                WHERE e.approved = :approved
                    AND f.active = :activeFeed
                ORDER BY e.dateModified DESC')
            ->setMaxResults($this->itemsPerPage)
            ->setFirstResult($offset)
            ->setParameter('approved', true)
            ->setParameter('activeFeed', true);

        return new Paginator($query);
    }

    public function deleteOlderThan(\DateTimeImmutable $threshold)
    {
        $this->_em->createQueryBuilder()
            ->delete(FeedEntry::class, 'e')
            ->where('e.dateModified < :threshold')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return FeedEntry[] Returns an array of FeedEntry objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FeedEntry
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
