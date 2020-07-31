<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\Task;
use App\Exception\MaxDepthException;
use App\Request\TaskRequest;
use App\Service\UserTasksProvider;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskHandler
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

    /**
     * @var UserTasksProvider
     */
    private $provider;

    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserTasksProvider $provider
    ) {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->provider = $provider;
    }

    public function getList(): Response
    {
        $usersModels = $this->provider->getUsersTasksModels();
        $context = SerializationContext::create()->setGroups(['list', 'Default']);
        $data = $this->serializer->serialize($usersModels, 'json', $context);

        return new Response($data, Response::HTTP_OK);
    }

    public function create(Request $request): Response
    {
        try {
            $taskRequest = new TaskRequest($request);
            $this->validate($taskRequest);
            $task = $this->saveTask($taskRequest);

            return new Response($task, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $exception) {
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, Task $task): Response
    {
        try {
            $taskRequest = new TaskRequest($request);
            $this->validate($taskRequest);
            $task = $this->saveTask($taskRequest, $task);

            return new Response($task, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $exception) {
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param TaskRequest $taskRequest
     * @throws Exception
     */
    private function validate(TaskRequest $taskRequest): void
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
    private function saveTask(TaskRequest $taskRequest, ?Task $task = null): string
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
            ->setPoints($taskRequest->getPoints())
            ->setIsDone($taskRequest->isDone())
            ->setUserId($taskRequest->getUserId())
            ->setLevel($level)
            ->setParent($parent);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->updateParent($parent);
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
}
