<?php

namespace App\Controller;

use App\Repository\QuizzRepository;
use App\Repository\ScoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LeaderboardController extends AbstractController
{
    #[Route('/leaderboard/{quizzId}', name: 'leaderboard', defaults: ['quizzId' => null])]
    public function index(?int $quizzId, QuizzRepository $quizzRepository, ScoreRepository $scoreRepository): Response
    {
        // Récupérer tous les quiz disponibles
        $quizzes = $quizzRepository->findAll();

        // Si aucun quiz n'est sélectionné, utilisez le premier quiz par défaut
        if ($quizzId === null && !empty($quizzes)) {
            $quizzId = $quizzes[0]->getId();
        }

        // Récupérer les scores pour le quiz sélectionné
        $scores = $scoreRepository->findBy(['IdQuizz' => $quizzId]);

        // Filtrer pour ne garder que le meilleur score par utilisateur
        $bestScores = [];
        foreach ($scores as $score) {
            $userId = $score->getIdUser()->getId();
            if (!isset($bestScores[$userId]) || $score->getUserScore() > $bestScores[$userId]->getUserScore()) {
                $bestScores[$userId] = $score;
            }
        }

        // Trier les scores par ordre décroissant
        usort($bestScores, function($a, $b) {
            return $b->getUserScore() <=> $a->getUserScore();
        });

        // Passer les quiz, les scores filtrés et triés, et un message si aucun score n'est trouvé
        return $this->render('leaderboard/index.html.twig', [
            'scores' => $bestScores,
            'quizzes' => $quizzes,
            'selectedQuizzId' => $quizzId,
            'noScoresMessage' => empty($bestScores) ? 'Aucun score trouvé pour ce quiz.' : null
        ]);
    }
}
