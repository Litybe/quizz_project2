<?php

namespace App\Entity;

use App\Repository\QuizzRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizzRepository::class)]
class Quizz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $Name;

    #[ORM\Column(length: 255)]
    private string $Description;

    #[ORM\Column(type: 'float')]
    private float $timeWeight;

    #[ORM\Column(type: 'float')]
    private float $correctAnswerWeight;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'IdQuizz')]
    private Collection $Questions;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'quizzes')]
    private Collection $tags;

    public function __construct()
    {
        $this->Questions = new ArrayCollection();
        $this->tags = new ArrayCollection();

    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->Questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->Questions->contains($question)) {
            $this->Questions->add($question);
            $question->setIdQuizz($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->Questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getIdQuizz() === $this) {
                $question->setIdQuizz(null);
            }
        }

        return $this;
    }

    public function getDescription(): string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): void
    {
        $this->Description = $Description;
    }

    public function getTimeWeight(): float
    {
        return $this->timeWeight;
    }

    public function setTimeWeight(float $timeWeight): self
    {
        $this->timeWeight = $timeWeight;
        return $this;
    }

    public function getCorrectAnswerWeight(): float
    {
        return $this->correctAnswerWeight;
    }

    public function setCorrectAnswerWeight(float $correctAnswerWeight): self
    {
        $this->correctAnswerWeight = $correctAnswerWeight;
        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
