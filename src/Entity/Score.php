<?php

namespace App\Entity;

use App\Repository\ScoreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScoreRepository::class)]
class Score
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?Quizz $IdQuizz = null;

    #[ORM\ManyToOne(inversedBy: 'Scores')]
    private ?User $IdUser = null;

    #[ORM\Column]
    private ?int $UserScore = null;

    function __construct(?Quizz $quizz, ?User $user, ?int $score)
    {
        $this->setIdQuizz($quizz);
        $this->setIdUser($user);
        $this->setUserScore($score);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdQuizz(): ?Quizz
    {
        return $this->IdQuizz;
    }

    public function setIdQuizz(?Quizz $IdQuizz): static
    {
        $this->IdQuizz = $IdQuizz;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->IdUser;
    }

    public function setIdUser(?User $IdUser): static
    {
        $this->IdUser = $IdUser;

        return $this;
    }

    public function getUserScore(): ?int
    {
        return $this->UserScore;
    }

    public function setUserScore(int $UserScore): static
    {
        $this->UserScore = $UserScore;

        return $this;
    }
}
