<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\AssignmentInterface;
use App\Entity\Task;

class UserAssignmentsModel
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $totalPoints;

    /**
     * @var int
     */
    private $donePoints;

    /**
     * @var AssignmentInterface[]
     */
    private $tasks;

    /**
     * @param string $name
     * @param int $totalPoints
     * @param int $donePoints
     * @param AssignmentInterface[] $tasks
     */
    public function __construct(string $name, int $totalPoints, int $donePoints, array $tasks)
    {
        $this->name = $name;
        $this->totalPoints = $totalPoints;
        $this->donePoints = $donePoints;
        $this->tasks = $tasks;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getTotalPoints(): int
    {
        return $this->totalPoints;
    }

    /**
     * @return int
     */
    public function getDonePoints(): int
    {
        return $this->donePoints;
    }

    /**
     * @return Task[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }
}
