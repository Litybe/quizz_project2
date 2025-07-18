<?php

namespace App\Model\Dto;

class UserResponseDto
{
    private bool $isCorrect;
    private float $timeTaken;

    public function __construct(bool $isCorrect, float $timeTaken)
    {
        $this->isCorrect = $isCorrect;
        $this->timeTaken = $timeTaken;
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function getTimeTaken(): float
    {
        return $this->timeTaken;
    }
}