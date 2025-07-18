<?php

namespace App\Model\Dto;

class ScoreWeightsDto
{
    private float $timeWeight;
    private float $correctAnswerWeight;

    public function __construct(float $timeWeight, float $correctAnswerWeight)
    {
        $this->timeWeight = $timeWeight;
        $this->correctAnswerWeight = $correctAnswerWeight;
    }

    public function getTimeWeight(): float
    {
        return $this->timeWeight;
    }

    public function getCorrectAnswerWeight(): float
    {
        return $this->correctAnswerWeight;
    }
}