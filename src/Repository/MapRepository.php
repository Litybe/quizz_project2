<?php

namespace App\Repository;

use App\Entity\Map;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Map>
 *
 * @method Map|null find($id, $lockMode = null, $lockVersion = null)
 * @method Map|null findOneBy(array $criteria, array $orderBy = null)
 * @method Map[]    findAll()
 * @method Map[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MapRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Map::class);
    }

    public function save(Map $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Map $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Map[] Returns an array of active maps
     */
    public function findActiveMaps(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.isActive = :val')
            ->setParameter('val', true)
            ->orderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Map[] Returns an array of maps with their strategy count
     */
    public function findMapsWithStrategyCount(): array
    {
        return $this->createQueryBuilder('m')
            ->select('m', 'COUNT(s.id) as strategyCount')
            ->leftJoin('m.strategies', 's')
            ->andWhere('m.isActive = :val')
            ->setParameter('val', true)
            ->groupBy('m.id')
            ->orderBy('strategyCount', 'DESC')
            ->addOrderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Map
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.slug = :val')
            ->setParameter('val', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
