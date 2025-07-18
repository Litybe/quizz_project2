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

    public function calculateAndSaveScore($quizzId, $content, SessionInterface $session, User $user)
    {
        $answerContent = $content['answers'];
        $timeContent = $content['responseTimes'];
        $title = "Bravo!";

        $userQuizzStatus = $this->entityManager->getRepository(UserQuizzStatus::class)->findOneBy([
            'User' => $user->getId(),
            'Quizz' => $quizzId
        ]);

        $existingScore = $this->entityManager->getRepository(Score::class)->findOneBy([
            'IdUser' => $user->getId(),
            'IdQuizz' => $quizzId
        ]);

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

        if ($existingScore) {
            if ($newScore > $existingScore->getUserScore()) {
                $title = "Best Score, BG le S!";
                $existingScore->setUserScore($newScore);
                $this->entityManager->persist($existingScore);
            } else {
                $title = "Même pas capable de faire mieux!";
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

        return [
            'score' => $newScore,
            'title' => $title
        ];
    }
}