<?php

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $TextAnswer = null;

    #[ORM\ManyToOne(inversedBy: 'Answers')]
    private ?Question $Question = null;

    #[ORM\ManyToOne(inversedBy: 'GoodAnswers')]
    private ?Question $GoodAnswers = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTextAnswer(): ?string
    {
        return $this->TextAnswer;
    }

    public function setTextAnswer(string $TextAnswer): static
    {
        $this->TextAnswer = $TextAnswer;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->Question;
    }

    public function setQuestion(?Question $Question): static
    {
        $this->Question = $Question;

        return $this;
    }

    public function getGoodAnswers(): ?Question
    {
        return $this->GoodAnswers;
    }

    public function setGoodAnswers(?Question $GoodAnswers): static
    {
        $this->GoodAnswers = $GoodAnswers;

        return $this;
    }
}
