<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\ScoreRepository;
use App\Repository\UserQuizzStatusRepository;

class QuizDisplayService
{
    private $userQuizzStatusRepository;
    private $scoreRepository;

    public function __construct(
        UserQuizzStatusRepository $userQuizzStatusRepository,
        ScoreRepository $scoreRepository
    ) {
        $this->userQuizzStatusRepository = $userQuizzStatusRepository;
        $this->scoreRepository = $scoreRepository;
    }

    /**
     * Récupère toutes les données d'affichage des quiz pour un utilisateur
     * @param User|null $user
     * @return array
     */
    public function getQuizDisplayData(?User $user): array
    {
        $userQuizzStatuses = [];
        $userScores = [];
        $userRankings = [];
        $bestScores = [];

        if ($user) {
            // Récupérer les statuts des quiz pour l'utilisateur
            $userQuizzStatuses = $this->userQuizzStatusRepository->findBy(["User" => $user]);
            $userQuizzStatuses = array_reduce($userQuizzStatuses, function ($carry, $status) {
                $carry[$status->getQuizz()->getId()] = $status;
                return $carry;
            }, []);
            
            // Récupérer les scores de l'utilisateur pour chaque quiz
            $userScores = $this->scoreRepository->findUserScoresIndexedByQuiz($user);
            
            // Récupérer le classement de l'utilisateur pour chaque quiz
            $userRankings = $this->scoreRepository->findUserRankingsByQuiz($user);
        }

        // Récupérer les meilleurs scores de tous les quiz
        $bestScores = $this->scoreRepository->findBestScoresByQuiz();

        return [
            'userQuizzStatuses' => $userQuizzStatuses,
            'userScores' => $userScores,
            'userRankings' => $userRankings,
            'bestScores' => $bestScores,
        ];
    }

    /**
     * Récupère les données d'affichage pour un quiz spécifique
     * @param User|null $user
     * @param int $quizId
     * @return array
     */
    public function getQuizDisplayDataForQuiz(?User $user, int $quizId): array
    {
        $data = $this->getQuizDisplayData($user);
        
        return [
            'isDone' => isset($data['userQuizzStatuses'][$quizId]) && $data['userQuizzStatuses'][$quizId]->getIsDone(),
            'userScore' => $data['userScores'][$quizId] ?? null,
            'userRanking' => $data['userRankings'][$quizId] ?? null,
            'bestScore' => $data['bestScores'][$quizId] ?? null,
        ];
    }
}
