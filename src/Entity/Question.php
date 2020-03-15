<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 */
class Question
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Unique(message="The {{ value }} title for question is repeated.")
     */
    public $title;

    /**
     * @ORM\Column(type="boolean")
     */
    public $promoted;

    /**
     * @ORM\Column(type="datetime")
     */
    public $created;

    /**
     * @ORM\Column(type="datetime")
     */
    public $updated;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('visible', 'invisible')")
     */
    public $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="question", orphanRemoval=true, cascade={"persist"})
     */
    private $answers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\QuestionHistory", mappedBy="question", orphanRemoval=true)
     */
    private $questionHistories;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->questionHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPromoted(): ?bool
    {
        return $this->promoted;
    }

    public function setPromoted(bool $promoted): self
    {
        $this->promoted = $promoted;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        if (!in_array($status, array(self::STATUS_DRAFT, self::STATUS_PUBLISHED))) {
            throw new \InvalidArgumentException("Invalid status, must be draft or published");
        }

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|QuestionHistory[]
     */
    public function getQuestionHistories(): Collection
    {
        return $this->questionHistories;
    }

    public function addQuestionHistory(QuestionHistory $questionHistory): self
    {
        if (!$this->questionHistories->contains($questionHistory)) {
            $this->questionHistories[] = $questionHistory;
            $questionHistory->setQuestion($this);
        }

        return $this;
    }

    public function removeQuestionHistory(QuestionHistory $questionHistory): self
    {
        if ($this->questionHistories->contains($questionHistory)) {
            $this->questionHistories->removeElement($questionHistory);
            // set the owning side to null (unless already changed)
            if ($questionHistory->getQuestion() === $this) {
                $questionHistory->setQuestion(null);
            }
        }

        return $this;
    }
}
