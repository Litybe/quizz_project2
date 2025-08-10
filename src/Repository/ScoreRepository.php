<?php

namespace App\Repository;

use App\Entity\Score;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Score>
 */
class ScoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Score::class);
    }

    public function findBestScoresByUser(User $user)
    {
        return $this->createQueryBuilder('s')
            ->where('s.IdUser = :user')
            ->setParameter('user', $user)
            ->orderBy('s.UserScore', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les scores d'un utilisateur indexés par l'ID du quiz
     * @param User $user
     * @return array
     */
    public function findUserScoresIndexedByQuiz(User $user): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('IDENTITY(s.IdQuizz) as quizId', 's.UserScore as score')
            ->where('s.IdUser = :user')
            ->setParameter('user', $user);

        $results = $qb->getQuery()->getResult();
        
        $scores = [];
        foreach ($results as $result) {
            $scores[$result['quizId']] = $result['score'];
        }
        
        return $scores;
    }

    /**
     * Récupère les meilleurs scores de tous les quiz
     * @return array
     */
    public function findBestScoresByQuiz(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('IDENTITY(s.IdQuizz) as quizId', 'MAX(s.UserScore) as bestScore')
            ->groupBy('s.IdQuizz');

        $results = $qb->getQuery()->getResult();
        
        $bestScores = [];
        foreach ($results as $result) {
            $bestScores[$result['quizId']] = $result['bestScore'];
        }
        
        return $bestScores;
    }

    /**
     * Récupère le classement de l'utilisateur pour chaque quiz
     * @param User $user
     * @return array
     */
    public function findUserRankingsByQuiz(User $user): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('IDENTITY(s.IdQuizz) as quizId', 's.UserScore as userScore')
            ->addSelect('(SELECT COUNT(s2.id) + 1 FROM App\Entity\Score s2 WHERE s2.IdQuizz = s.IdQuizz AND s2.UserScore > s.UserScore) as rank')
            ->where('s.IdUser = :user')
            ->setParameter('user', $user);

        $results = $qb->getQuery()->getResult();
        
        $rankings = [];
        foreach ($results as $result) {
            $rankings[$result['quizId']] = $result['rank'];
        }
        
        return $rankings;
    }

    //    /**
    //     * @return Score[] Returns an array of Score objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Score
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
