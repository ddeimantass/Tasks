<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\Task;
use App\Request\TaskRequest;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskHandler
{
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

    public function getList(): Response
    {
        $tasks = $this->entityManager->getRepository(Task::class)->findAll();
        $data = $this->serializer->serialize($tasks, 'json');

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

    private function saveTask(TaskRequest $taskRequest, ?Task $task = null): string
    {
        if (null === $task) {
            $task = new Task();
        }

        /** @var Task $parent */
        $parent = $this->entityManager->find(Task::class, $taskRequest->getParentId());

        $task->setTitle($taskRequest->getTitle())
            ->setPoints($taskRequest->getPoints())
            ->setIsDone($taskRequest->isDone())
            ->setUserId($taskRequest->getUserId())
            ->setParent($parent);

        $this->entityManager->persist($task);
        $this->entityManager->flush();


        return $this->serializer->serialize($task, 'json');
    }
}
