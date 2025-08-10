<?php

namespace App\Service;

use App\Entity\Quizz;
use App\Entity\Score;
use App\Entity\User;
use App\Entity\UserQuizzStatus;
use App\Model\Dto\QuestionDto;
use App\Model\Dto\ScoreWeightsDto;
use App\Model\Dto\UserResponseDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ScoreService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function calculateAndSaveScore($quizzId, $content, SessionInterface $session, ?User $user = null)
    {
        $answerContent = $content['answers'];
        $timeContent = $content['responseTimes'];
        $title = "Bravo!";
        $isGuest = $user === null;

        // Si c'est un utilisateur connecté, récupérer les données existantes
        if (!$isGuest) {
            $userQuizzStatus = $this->entityManager->getRepository(UserQuizzStatus::class)->findOneBy([
                'User' => $user->getId(),
                'Quizz' => $quizzId
            ]);

            $existingScore = $this->entityManager->getRepository(Score::class)->findOneBy([
                'IdUser' => $user->getId(),
                'IdQuizz' => $quizzId
            ]);
        }

        $quizz = $this->entityManager->getRepository(Quizz::class)->find($quizzId);
        $questions = $quizz->getQuestions();
        $timeWeight = $quizz->getTimeWeight() ?? 0.2;
        $correctAnswerWeight = $quizz->getCorrectAnswerWeight() ?? 0.8;

        $questionDTOs = [];
        $userResponseDtos = [];

        foreach ($questions as $question) {
            $questionId = $question->getId();
            $questionDTOs[] = new QuestionDto(15); // $question->getMaxTime()

            $isCorrect = false;
            $timeTaken = 0;

            if ($question->isTextual()) {
                $selectedAnswer = $answerContent[$questionId] ?? '';
                $correctAnswer = $question->getCorrectTextualAnswer();
                $isCorrect = strtolower($selectedAnswer) === strtolower($correctAnswer);
            } else {
                $selectedAnswers = $answerContent[$questionId] ?? [];
                $goodAnswers = $question->getGoodAnswers();
                $allCorrect = true;

                foreach ($selectedAnswers as $selectedAnswerId) {
                    $isCorrect = false;
                    foreach ($goodAnswers as $goodAnswer) {
                        if ($goodAnswer->getId() == $selectedAnswerId) {
                            $isCorrect = true;
                            break;
                        }
                    }
                    if (!$isCorrect) {
                        $allCorrect = false;
                        break;
                    }
                }
                $isCorrect = $allCorrect && count($selectedAnswers) == count($goodAnswers);
            }

            $startTime = $timeContent[$questionId]['startTime'] ?? 0;
            $endTime = $timeContent[$questionId]['endTime'] ?? 0;
            $timeTaken = strtotime($endTime) - strtotime($startTime);

            $userResponseDtos[] = new UserResponseDto($isCorrect, $timeTaken);
        }

        $scoreWeightsDTO = new ScoreWeightsDto($timeWeight, $correctAnswerWeight); // 20% pour le temps, 80% pour les bonnes réponses
        $quizScoreCalculatorService = new QuizzScoreCalculatorService($scoreWeightsDTO);
        $newScore = $quizScoreCalculatorService->calculateScore($questionDTOs, $userResponseDtos);

        // Si c'est un utilisateur connecté, sauvegarder le score
        if (!$isGuest) {
            if ($existingScore) {
                if ($newScore > $existingScore->getUserScore()) {
                    $existingScore->setUserScore($newScore);
                    $this->entityManager->persist($existingScore);
                }
            } else {
                $scoreData = new Score($quizz, $user, $newScore);
                $this->entityManager->persist($scoreData);
            }

            if (!$userQuizzStatus) {
                $userQuizzStatus = new UserQuizzStatus();
                $userQuizzStatus->setUser($user);
                $userQuizzStatus->setQuizz($quizz);
            }

            $userQuizzStatus->setIsDone(true);
            $this->entityManager->persist($userQuizzStatus);
            $this->entityManager->flush();
        } else {
            // Pour les utilisateurs non connectés, sauvegarder temporairement en session
            $tempScoreData = [
                'quizzId' => $quizzId,
                'score' => $newScore,
                'answers' => $answerContent,
                'responseTimes' => $timeContent,
                'timestamp' => time()
            ];
            $session->set('temp_quiz_score', $tempScoreData);
            
            $title = "Score temporaire - Connectez-vous pour sauvegarder !";
        }

        return [
            'score' => $newScore,
            'title' => $title,
            'isGuest' => $isGuest,
            'quizzId' => $quizzId
        ];
    }

    /**
     * Récupère et sauvegarde le score temporaire d'un utilisateur non connecté
     */
    public function saveTemporaryScore(SessionInterface $session, User $user): ?array
    {
        $tempScoreData = $session->get('temp_quiz_score');
        
        if (!$tempScoreData) {
            return null; // Aucun score temporaire
        }

        // Vérifier que le score n'est pas trop ancien (24h max)
        if (time() - $tempScoreData['timestamp'] > 86400) {
            $session->remove('temp_quiz_score');
            return null; // Score trop ancien
        }

        try {
            // Sauvegarder le score avec l'utilisateur maintenant connecté
            $quizz = $this->entityManager->getRepository(Quizz::class)->find($tempScoreData['quizzId']);
            
            if (!$quizz) {
                $session->remove('temp_quiz_score');
                return null; // Quiz introuvable
            }

            // Vérifier si l'utilisateur a déjà un score pour ce quiz
            $existingScore = $this->entityManager->getRepository(Score::class)->findOneBy([
                'IdUser' => $user->getId(),
                'IdQuizz' => $tempScoreData['quizzId']
            ]);

            if ($existingScore) {
                // Mettre à jour le score si le nouveau est meilleur
                if ($tempScoreData['score'] > $existingScore->getUserScore()) {
                    $existingScore->setUserScore($tempScoreData['score']);
                    $this->entityManager->persist($existingScore);
                }
                $title = "Score temporaire sauvegardé !";
            } else {
                // Créer un nouveau score
                $scoreData = new Score($quizz, $user, $tempScoreData['score']);
                $this->entityManager->persist($scoreData);
                $title = "Score temporaire sauvegardé !";
            }

            // Mettre à jour le statut du quiz
            $userQuizzStatus = $this->entityManager->getRepository(UserQuizzStatus::class)->findOneBy([
                'User' => $user->getId(),
                'Quizz' => $tempScoreData['quizzId']
            ]);

            if (!$userQuizzStatus) {
                $userQuizzStatus = new UserQuizzStatus();
                $userQuizzStatus->setUser($user);
                $userQuizzStatus->setQuizz($quizz);
            }

            $userQuizzStatus->setIsDone(true);
            $this->entityManager->persist($userQuizzStatus);
            $this->entityManager->flush();

            // Nettoyer la session
            $session->remove('temp_quiz_score');

            return [
                'score' => $tempScoreData['score'],
                'title' => $title,
                'quizzId' => $tempScoreData['quizzId']
            ];

        } catch (\Exception $e) {
            // En cas d'erreur, nettoyer la session
            $session->remove('temp_quiz_score');
            return null;
        }
    }
}