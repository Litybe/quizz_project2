<?php

namespace App\Entity;

use App\Repository\UserQuizzStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserQuizzStatusRepository::class)]
class UserQuizzStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $Id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'UserQuizzStatuses')]
    private ?User $User = null;

    #[ORM\ManyToOne(targetEntity: Quizz::class)]
    private ?Quizz $Quizz = null;

    #[ORM\Column(type: 'boolean')]
    private bool $IsDone = false;

    public function getId(): ?int
    {
        return $this->Id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $user): self
    {
        $this->User = $user;
        return $this;
    }

    public function getQuizz(): ?Quizz
    {
        return $this->Quizz;
    }

    public function setQuizz(?Quizz $quizz): self
    {
        $this->Quizz = $quizz;
        return $this;
    }

    public function getIsDone(): bool
    {
        return $this->IsDone;
    }

    public function setIsDone(bool $isDone): self
    {
        $this->IsDone = $isDone;
        return $this;
    }
}
