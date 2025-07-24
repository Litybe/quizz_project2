<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $QuestionText = null;

    /**
     * @var Collection<int, Answer>
     */
    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'Question', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $Answers;

    /**
     * @var Collection<int, Answer>
     */
    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'GoodAnswers', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $GoodAnswers;

    #[ORM\ManyToOne(targetEntity: Quizz::class, inversedBy: 'Questions')]
    private ?Quizz $IdQuizz = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $ImagePath;

    // Nouveau champ pour indiquer si la question est textuelle
    #[ORM\Column(type: 'boolean')]
    private bool $IsTextual = false;

    // Nouveau champ pour stocker la réponse correcte pour les questions textuelles
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $CorrectTextualAnswer = null;

    public function __construct()
    {
        $this->Answers = new ArrayCollection();
        $this->GoodAnswers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestionText(): ?string
    {
        return $this->QuestionText;
    }

    public function setQuestionText(string $QuestionText): static
    {
        $this->QuestionText = $QuestionText;

        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->Answers;
    }

    public function addAnswer(Answer $answer): static
    {
        if (!$this->Answers->contains($answer)) {
            $this->Answers->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): static
    {
        if ($this->Answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getGoodAnswers(): Collection
    {
        return $this->GoodAnswers;
    }

    public function addGoodAnswer(Answer $goodAnswer): static
    {
        if (!$this->GoodAnswers->contains($goodAnswer)) {
            $this->GoodAnswers->add($goodAnswer);
            $goodAnswer->setGoodAnswers($this);
        }

        return $this;
    }

    public function clearGoodAnswers(): static
    {
        $this->GoodAnswers->clear();
        return $this;
    }
    public function removeGoodAnswer(Answer $goodAnswer): static
    {
        if ($this->GoodAnswers->removeElement($goodAnswer)) {
            // set the owning side to null (unless already changed)
            if ($goodAnswer->getGoodAnswers() === $this) {
                $goodAnswer->setGoodAnswers(null);
            }
        }

        return $this;
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

    public function getImagePath(): ?string
    {
        return $this->ImagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->ImagePath = $imagePath;

        return $this;
    }

    public function isTextual(): bool
    {
        return $this->IsTextual;
    }

    public function setIsTextual(bool $isTextual): self
    {
        $this->IsTextual = $isTextual;
        return $this;
    }

    public function getCorrectTextualAnswer(): ?string
    {
        return $this->CorrectTextualAnswer;
    }

    public function setCorrectTextualAnswer(?string $correctTextualAnswer): self
    {
        $this->CorrectTextualAnswer = $correctTextualAnswer;
        return $this;
    }
}
