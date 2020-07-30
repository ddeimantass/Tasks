<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class TaskRequest
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var int|null
     */
    private $parentId;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    private $userId;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Range(
     *      min = 1,
     *      max = 10,
     *      notInRangeMessage = "Points must be between {{ min }} and {{ max }} in value",
     * )
     */
    private $points;

    /**
     * @var int
     * @Assert\NotNull()
     * @Assert\Range(
     *      min = 0,
     *      max = 1,
     *      notInRangeMessage = "Value must be {{ min }} or {{ max }}",
     * )
     */
    private $isDone;

    public function __construct(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->title = $data['title'] ?? null;
        $this->points = $data['points'] ?? null;
        $this->isDone = $data['is_done'] ?? null;
        $this->parentId = $data['parent_id'] ?? null;
        $this->userId = $data['user_id'] ?? null;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function isDone(): bool
    {
        return (bool)$this->isDone;
    }
}
