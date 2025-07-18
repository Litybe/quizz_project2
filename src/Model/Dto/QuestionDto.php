<?php

namespace App\Model\Dto;

class QuestionDto
{
    private float $maxTime;

    public function __construct(float $maxTime)
    {
        $this->maxTime = $maxTime;
    }

    public function getMaxTime(): float
    {
        return $this->maxTime;
    }
}