<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\Task;
use App\Request\TaskRequest;
use App\Service\TaskService;
use App\Service\UserAssignmentProvider;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @var TaskService
     */
    private $taskService;

    /**
     * @var UserAssignmentProvider
     */
    private $provider;

    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        TaskService $taskService,
        UserAssignmentProvider $provider
    ) {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->taskService = $taskService;
        $this->provider = $provider;
    }

    public function getList(): Response
    {
        $mainTasks = $this->entityManager->getRepository(Task::class)->findBy(['parent' => null]);
        $usersModels = $this->provider->getUsersAssigmentModels($mainTasks);
        $context = SerializationContext::create()->setGroups(['list', 'Default']);
        $data = $this->serializer->serialize($usersModels, 'json', $context);

        return new Response($data, Response::HTTP_OK);
    }

    public function create(Request $request): Response
    {
        try {
            $taskRequest = new TaskRequest($request);
            $this->taskService->validate($taskRequest);
            $task = $this->taskService->saveTask($taskRequest);

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
            $this->taskService->validate($taskRequest);
            $task = $this->taskService->saveTask($taskRequest, $task);

            return new Response($task, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $exception) {
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
