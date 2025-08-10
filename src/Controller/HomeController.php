<?php

namespace App\Controller;

use App\Repository\QuizzRepository;
use App\Service\QuizDisplayService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(QuizzRepository $quizzRepository, QuizDisplayService $quizDisplayService): Response
    {
        // Récupère le dernier quiz mis en ligne
        $lastQuizz = $quizzRepository->findOneBy([], ['id' => 'DESC']);

        // Récupérer les données d'affichage pour le dernier quiz
        $user = $this->getUser();
        $quizDisplayData = null;
        
        if ($lastQuizz && $user) {
            $quizDisplayData = $quizDisplayService->getQuizDisplayDataForQuiz($user, $lastQuizz->getId());
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

        return $this->render('home/index.html.twig', [
            'lastQuizz' => $lastQuizz,
            'userQuizzStatuses' => $userQuizzStatuses,
            'userScores' => $userScores,
            'userRankings' => $userRankings,
            'bestScores' => $bestScores
        ]);
    }
}