<?php

namespace App\Repository;

use App\Entity\StrategyPosition;
use App\Entity\Strategy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StrategyPosition>
 *
 * @method StrategyPosition|null find($id, $lockMode = null, $lockVersion = null)
 * @method StrategyPosition|null findOneBy(array $criteria, array $orderBy = null)
 * @method StrategyPosition[]    findAll()
 * @method StrategyPosition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StrategyPositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StrategyPosition::class);
    }

    public function save(StrategyPosition $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StrategyPosition $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return StrategyPosition[] Returns an array of positions for a specific strategy
     */
    public function findByStrategy(Strategy $strategy): array
    {
        return $this->createQueryBuilder('sp')
            ->andWhere('sp.strategy = :strategy')
            ->setParameter('strategy', $strategy)
            ->orderBy('sp.playerNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return StrategyPosition[] Returns an array of positions by player number
     */
    public function findByPlayerNumber(int $playerNumber, Strategy $strategy): array
    {
        return $this->createQueryBuilder('sp')
            ->andWhere('sp.playerNumber = :playerNumber')
            ->andWhere('sp.strategy = :strategy')
            ->setParameter('playerNumber', $playerNumber)
            ->setParameter('strategy', $strategy)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return StrategyPosition[] Returns an array of positions by role
     */
    public function findByRole(string $role, Strategy $strategy): array
    {
        return $this->createQueryBuilder('sp')
            ->andWhere('sp.role = :role')
            ->andWhere('sp.strategy = :strategy')
            ->setParameter('role', $role)
            ->setParameter('strategy', $strategy)
            ->orderBy('sp.playerNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
