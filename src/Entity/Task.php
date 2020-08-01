<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaskRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task implements AssignmentInterface
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $points;

    /**
     * @var bool
     * @JMS\Type("int")
     * @ORM\Column(type="boolean")
     */
    private $isDone;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @var Task|null
     * @JMS\Exclude
     * @ORM\ManyToOne(targetEntity="Task", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id")
     */
    private $parent;

    /**
     * @var Task[]
     * @JMS\Groups({"list"})
     * @ORM\OneToMany(targetEntity="Task", mappedBy="parent")
     */
    private $children;

    /**
     * @var DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var int
     * @JMS\Exclude
     * @ORM\Column(type="integer")
     */
    private $level;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;

        return $this;
    }

    public function getIsDone(): ?bool
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone): self
    {
        $this->isDone = $isDone;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getParent(): ?Task
    {
        return $this->parent;
    }

    public function setParent(?Task $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @param Task[] $children
     * @return $this
     */
    public function setChildren(array $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("parent_id")
     */
    public function getParentId(): ?int
    {
        return $this->parent ? $this->parent->getId() : null;
    }

    public function getDonePoints(): int
    {
        $done = 0;
        foreach ($this->children as $child) {
            if ($child->isDone) {
                $done += $child->getPoints();
                continue;
            }

            if (\count($child->getChildren()) > 0) {
                $done += $child->getDonePoints();
            }
        }

        return $done;
    }
}
