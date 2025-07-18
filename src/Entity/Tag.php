<?php

// src/Entity/Tag.php
namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Course::class, mappedBy: 'tags')]
    private Collection $courses;

    #[ORM\ManyToMany(targetEntity: Quizz::class, mappedBy: 'tags')]
    private Collection $quizzes;

    public function __construct()
    {
        $this->courses = new ArrayCollection();
        $this->quizzes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection<int, Course>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): static
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
            $course->addTag($this);
        }

        return $this;
    }

    public function removeCourse(Course $course): static
    {
        if ($this->courses->removeElement($course)) {
            $course->removeTag($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Quizz>
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quizz $quizz): static
    {
        if (!$this->quizzes->contains($quizz)) {
            $this->quizzes->add($quizz);
            $quizz->addTag($this);
        }

        return $this;
    }

    public function removeQuiz(Quizz $quizz): static
    {
        if ($this->quizzes->removeElement($quizz)) {
            $quizz->removeTag($this);
        }

        return $this;
    }
}
