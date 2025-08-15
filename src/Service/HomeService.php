<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\QuizzRepository;

class HomeService
{
    public function __construct(
        private QuizzRepository $quizzRepository,
        private QuizDisplayService $quizDisplayService
    ) {}

    public function getHomePageData(?User $user): array
    {
        // Récupère le dernier quiz mis en ligne
        $lastQuizz = $this->quizzRepository->findOneBy([], ['id' => 'DESC']);

        // Récupérer les données d'affichage pour le dernier quiz
        $quizDisplayData = null;
        
        if ($lastQuizz && $user) {
            $quizDisplayData = $this->quizDisplayService->getQuizDisplayDataForQuiz($user, $lastQuizz->getId());
        }

        // Préparer les données dans le format attendu par le partial _quiz_card.html.twig
        $userQuizzStatuses = [];
        $userScores = [];
        $userRankings = [];
        $bestScores = [];

        if ($lastQuizz && $quizDisplayData) {
            $quizId = $lastQuizz->getId();
            
            // Créer les structures indexées par quiz ID
            if (isset($quizDisplayData['isDone']) && $quizDisplayData['isDone']) {
                // Créer un objet avec la méthode getIsDone()
                $userQuizzStatuses[$quizId] = new class {
                    public function getIsDone() { return true; }
                };
            }
            
            if (isset($quizDisplayData['userScore']) && $quizDisplayData['userScore'] !== null) {
                $userScores[$quizId] = $quizDisplayData['userScore'];
            }
            
            if (isset($quizDisplayData['userRanking']) && $quizDisplayData['userRanking'] !== null) {
                $userRankings[$quizId] = $quizDisplayData['userRanking'];
            }
            
            if (isset($quizDisplayData['bestScore']) && $quizDisplayData['bestScore'] !== null) {
                $bestScores[$quizId] = $quizDisplayData['bestScore'];
            }
        }

        return [
            'lastQuizz' => $lastQuizz,
            'userQuizzStatuses' => $userQuizzStatuses,
            'userScores' => $userScores,
            'userRankings' => $userRankings,
            'bestScores' => $bestScores
        ];
    }
}
