<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Task;
use App\Exception\MaxDepthException;
use App\Request\TaskRequest;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskService
{
    private const MAX_DEPTH = 5;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * @param TaskRequest $taskRequest
     * @throws Exception
     */
    public function validate(TaskRequest $taskRequest): void
    {
        $violations = $this->validator->validate($taskRequest);

        if (0 !== \count($violations)) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = [
                    'message' => $violation->getMessage(),
                ];
            }

            $data = $this->serializer->serialize($errors, 'json');

            throw new Exception($data);
        }
    }

    /**
     * @param TaskRequest $taskRequest
     * @param Task|null $task
     *
     * @return string
     * @throws MaxDepthException
     */
    public function saveTask(TaskRequest $taskRequest, ?Task $task = null): string
    {
        if (null === $task) {
            $task = new Task();
        }

        $parent = null;
        $level = 1;
        if ($taskRequest->getParentId()) {
            /** @var Task $parent */
            $parent = $this->entityManager->find(Task::class, $taskRequest->getParentId());
            $level += $parent->getLevel();
        }

        if ($level > self::MAX_DEPTH) {
            throw new MaxDepthException();
        }

        $task->setTitle($taskRequest->getTitle())
            ->setPoints($task->hasChildren() ? $task->getPoints() : $taskRequest->getPoints())
            ->setIsDone($taskRequest->isDone())
            ->setUserId($taskRequest->getUserId())
            ->setLevel($level)
            ->setParent($parent);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->updateParent($parent);
        $this->updateChildren($task->getChildren()->toArray(), $task->getIsDone());

        $context = SerializationContext::create()->setGroups(['Default']);

        return $this->serializer->serialize($task, 'json', $context);
    }

    private function updateParent(?Task $parent): void
    {
        if (null === $parent) {
            return;
        }

        $children = $parent->getChildren();
        $isDone = true;
        $points = 0;

        foreach ($children as $child) {
            $points += $child->getPoints();
            $isDone = $isDone && $child->getIsDone();
        }

        $parent->setPoints($points)
            ->setIsDone($isDone);

        $this->entityManager->persist($parent);
        $this->entityManager->flush();

        $this->updateParent($parent->getParent());
    }

    /**
     * @param Task[] $children
     * @param bool $isDone
     */
    private function updateChildren(array $children, bool $isDone): void
    {
        if (0 === \count($children)) {
            return;
        }

        foreach ($children as $child) {
            $this->updateChildren($child->getChildren()->toArray(), $isDone);
            $child->setIsDone($isDone);
            $this->entityManager->persist($child);
        }

        $this->entityManager->flush();
    }
}
