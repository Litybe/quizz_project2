<?php

namespace App\Repository;

use App\Entity\Strategy;
use App\Entity\Map;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Strategy>
 *
 * @method Strategy|null find($id, $lockMode = null, $lockVersion = null)
 * @method Strategy|null findOneBy(array $criteria, array $orderBy = null)
 * @method Strategy[]    findAll()
 * @method Strategy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StrategyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Strategy::class);
    }

    public function save(Strategy $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Strategy $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Strategy[] Returns an array of public strategies
     */
    public function findPublicStrategies(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isPublic = :val')
            ->setParameter('val', true)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Strategy[] Returns an array of strategies for a specific map
     */
    public function findByMap(Map $map): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.map = :map')
            ->andWhere('s.isPublic = :val')
            ->setParameter('map', $map)
            ->setParameter('val', true)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Strategy[] Returns an array of strategies by side (T or CT)
     */
    public function findBySide(string $side): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.side = :side')
            ->andWhere('s.isPublic = :val')
            ->setParameter('side', $side)
            ->setParameter('val', true)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Strategy[] Returns an array of strategies by difficulty
     */
    public function findByDifficulty(string $difficulty): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.difficulty = :difficulty')
            ->andWhere('s.isPublic = :val')
            ->setParameter('difficulty', $difficulty)
            ->setParameter('val', true)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Strategy[] Returns an array of strategies by author
     */
    public function findByAuthor(User $author): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.author = :author')
            ->orderBy('s.createdAt', 'DESC')
            ->setParameter('author', $author)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Strategy[] Returns an array of strategies with search
     */
    public function searchStrategies(string $search, ?Map $map = null, ?string $side = null, ?string $difficulty = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.isPublic = :val')
            ->setParameter('val', true);

        if ($search) {
            $qb->andWhere('s.title LIKE :search OR s.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($map) {
            $qb->andWhere('s.map = :map')
                ->setParameter('map', $map);
        }

        if ($side) {
            $qb->andWhere('s.side = :side')
                ->setParameter('side', $side);
        }

        if ($difficulty) {
            $qb->andWhere('s.difficulty = :difficulty')
                ->setParameter('difficulty', $difficulty);
        }

        return $qb->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Strategy[] Returns an array of recent strategies
     */
    public function findRecentStrategies(int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isPublic = :val')
            ->setParameter('val', true)
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
