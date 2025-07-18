<?php

namespace App\Service;

use App\Model\Dto\QuestionDto;
use App\Model\Dto\ScoreWeightsDto;
use App\Model\Dto\UserResponseDto;

class QuizzScoreCalculatorService
{
    private $timeWeight;
    private $correctAnswerWeight;

    public function __construct(ScoreWeightsDto $scoreWeightsDto)
    {
        $this->timeWeight = $scoreWeightsDto->getTimeWeight();
        $this->correctAnswerWeight = $scoreWeightsDto->getCorrectAnswerWeight();
    }

    public function calculateScore(array $questions, array $userResponses): float
    {
        $totalScore = 0;
        $numberOfQuestions = count($questions);
        $maxTimeScorePerQuestion = 100 / $numberOfQuestions;
        $maxCorrectAnswerScorePerQuestion = 100 / $numberOfQuestions;

        foreach ($questions as $index => $questionDto) {
            $responseDto = $userResponses[$index] ?? null;

            $correctAnswerScore = $this->calculateCorrectAnswerScore($questionDto, $responseDto, $maxCorrectAnswerScorePerQuestion);
            $timeScore = $this->calculateTimeScore($questionDto, $responseDto, $maxTimeScorePerQuestion);

            $totalScore += ($correctAnswerScore * $this->correctAnswerWeight) + ($timeScore * $this->timeWeight);
        }

        return min($totalScore, 100);
    }

    private function calculateCorrectAnswerScore(QuestionDto $questionDto, ?UserResponseDto $responseDto, float $maxScore): float
    {
        if ($responseDto === null || !$responseDto->isCorrect()) {
            return 0;
        }

        return $maxScore;
    }

    private function calculateTimeScore(QuestionDto $questionDto, ?UserResponseDto $responseDto, float $maxScore): float
    {
        if ($responseDto === null) {
            return 0;
        }

        $timeTaken = $responseDto->getTimeTaken();
        $maxTime = $questionDto->getMaxTime();

        $timeScore = $maxScore * (1 - ($timeTaken / $maxTime));

        return max($timeScore, 0);
    }
}