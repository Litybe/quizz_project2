<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['Email'], message: 'There is already an account with this Email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Pseudo = null;

    #[ORM\Column(length: 255)]
    private ?string $Email = null;

    #[ORM\Column(length: 255)]
    private ?string $FaceitPseudo = null;

    #[ORM\Column(length: 255)]
    private ?string $FaceitPlayerId = null;

    #[ORM\Column(length: 255)]
    private string $Password;

    /**
     * @var Collection<int, Score>
     */
    #[ORM\OneToMany(targetEntity: Score::class, mappedBy: 'IdUser')]
    private Collection $Scores;

    #[ORM\Column(type: 'json')]
    private array $Roles = [];

    /**
     * @var Collection<int, UserQuizzStatus>
     */
    #[ORM\OneToMany(targetEntity: UserQuizzStatus::class, mappedBy: 'User')]
    private Collection $UserQuizzStatuses;

    ///**
    // * @var Collection<int, UserCourseStatus>
    // */
    //#[ORM\OneToMany(targetEntity: UserCourseStatus::class, mappedBy: 'user')]
    //private Collection $UserCourseStatuses;


    public function __construct()
    {
        $this->Scores = new ArrayCollection();
        $this->UserQuizzStatuses = new ArrayCollection();
        //$this->UserCourseStatuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->Pseudo;
    }

    public function setPseudo(string $Pseudo): static
    {
        $this->Pseudo = $Pseudo;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): static
    {
        $this->Email = $Email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->Password;
    }

    public function setPassword(string $Password): static
    {
        $this->Password = $Password;

        return $this;
    }

    public function getFaceitPseudo(): ?string
    {
        return $this->FaceitPseudo;
    }

    public function setFaceitPseudo(string $faceitPseudo): static
    {
        $this->FaceitPseudo = $faceitPseudo;

        return $this;
    }

    public function getFaceitPlayerId(): ?string
    {
        return $this->FaceitPlayerId;
    }

    public function setFaceitPlayerId(string $faceitPlayerId): static
    {
        $this->FaceitPlayerId = $faceitPlayerId;

        return $this;
    }
    
    /**
     * @return Collection<int, Score>
     */
    public function getScores(): Collection
    {
        return $this->Scores;
    }

    public function addScore(Score $score): static
    {
        if (!$this->Scores->contains($score)) {
            $this->Scores->add($score);
        }

        return $this;
    }

    public function removeScore(Score $score): static
    {
        if ($this->Scores->removeElement($score)) {
            // set the owning side to null (unless already changed)
            if ($score->getIdUser() === $this) {
                $score->setIdUser(null);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->Roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->Roles = $roles;

        return $this;
    }

    public function removeRole(Role $role): static
    {
        $this->Roles->removeElement($role);

        return $this;
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        // TODO: Implement getUserIdentifier() method.
        return $this->Pseudo;
    }

    /**
     * @return Collection<int, UserQuizzStatus>
     */
    public function getUserQuizzStatuses(): Collection
    {
        return $this->UserQuizzStatuses;
    }

    public function addUserQuizzStatus(UserQuizzStatus $userQuizzStatus): self
    {
        if (!$this->UserQuizzStatuses->contains($userQuizzStatus)) {
            $this->UserQuizzStatuses[] = $userQuizzStatus;
            $userQuizzStatus->setUser($this);
        }

        return $this;
    }

    public function removeUserQuizzStatus(UserQuizzStatus $userQuizzStatus): self
    {
        $this->UserQuizzStatuses->removeElement($userQuizzStatus);

        return $this;
    }

    /**
    // * @return Collection<int, UserCourseStatus>
     */
    /*public function getUserCourseStatuses(): Collection
    {
        return $this->UserCourseStatuses;
    }

    public function addUserCourseStatus(UserCourseStatus $userCourseStatus): self
    {
        if (!$this->UserCourseStatuses->contains($userCourseStatus)) {
            $this->UserCourseStatuses[] = $userCourseStatus;
            $userCourseStatus->setUser($this);
        }

        return $this;
    }

    public function removeUserCourseStatus(UserCourseStatus $userCourseStatus): self
    {
        if ($this->UserCourseStatuses->removeElement($userCourseStatus)) {
            // set the owning side to null (unless already changed)
            if ($userCourseStatus->getUser() === $this) {
                $userCourseStatus->setUser(null);
            }
        }

        return $this;
    }*/
}
