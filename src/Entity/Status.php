<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column]
private ?int $id = null;

#[ORM\Column(length: 16)]
private ?string $libelle = null;

/**
 * @var Collection<int, Task>
 */
#[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'status', orphanRemoval: true)]
private Collection $tasks;

public function __construct()
{
    $this->tasks = new ArrayCollection();
}

public const STATUS_TODO = 'To Do';
public const STATUS_DOING = 'Doing';
public const STATUS_DONE = 'Done';

public static function getAvailableStatuses(): array
{
return [
self::STATUS_TODO,
self::STATUS_DOING,
self::STATUS_DONE,
];
}

public function getId(): ?int
{
return $this->id;
}

public function getLibelle(): ?string
{
return $this->libelle;
}

public function setLibelle(string $libelle): static
{
if (!in_array($libelle, self::getAvailableStatuses())) {
throw new \InvalidArgumentException("Invalid status");
}
$this->libelle = $libelle;

return $this;
}

/**
 * @return Collection<int, Task>
 */
public function getTasks(): Collection
{
    return $this->tasks;
}

public function addTask(Task $task): static
{
    if (!$this->tasks->contains($task)) {
        $this->tasks->add($task);
        $task->setStatus($this);
    }

    return $this;
}

public function removeTask(Task $task): static
{
    if ($this->tasks->removeElement($task)) {
        // set the owning side to null (unless already changed)
        if ($task->getStatus() === $this) {
            $task->setStatus(null);
        }
    }

    return $this;
}
}
