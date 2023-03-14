<?php

namespace App\Entity;

use App\Repository\TasksRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TasksRepository::class)]
class Tasks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?TaskTypes $taskType = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?Forms $form = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $taskDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $visitDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(nullable: true)]
    private ?int $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $modified = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: FormVisitData::class)]
    private Collection $formVisitData;

    public function __construct()
    {
        $this->formVisitData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTaskType(): ?TaskTypes
    {
        return $this->taskType;
    }

    public function setTaskType(?TaskTypes $taskType): self
    {
        $this->taskType = $taskType;

        return $this;
    }

    public function getForm(): ?Forms
    {
        return $this->form;
    }

    public function setForm(?Forms $form): self
    {
        $this->form = $form;

        return $this;
    }

    public function getTaskDate(): ?\DateTimeInterface
    {
        return $this->taskDate;
    }

    public function setTaskDate(\DateTimeInterface $taskDate): self
    {
        $this->taskDate = $taskDate;

        return $this;
    }

    public function getVisitDate(): ?\DateTimeInterface
    {
        return $this->visitDate;
    }

    public function setVisitDate(?\DateTimeInterface $visitDate): self
    {
        $this->visitDate = $visitDate;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(\DateTimeInterface $modified): self
    {
        $this->modified = $modified;

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

    /**
     * @return Collection<int, FormVisitData>
     */
    public function getFormVisitData(): Collection
    {
        return $this->formVisitData;
    }

    public function addFormVisitData(FormVisitData $formVisitData): self
    {
        if (!$this->formVisitData->contains($formVisitData)) {
            $this->formVisitData->add($formVisitData);
            $formVisitData->setTask($this);
        }

        return $this;
    }

    public function removeFormVisitData(FormVisitData $formVisitData): self
    {
        if ($this->formVisitData->removeElement($formVisitData)) {
            // set the owning side to null (unless already changed)
            if ($formVisitData->getTask() === $this) {
                $formVisitData->setTask(null);
            }
        }

        return $this;
    }
}
