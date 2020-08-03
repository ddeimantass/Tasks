<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\DTO\UserModel;
use App\Entity\Task;
use App\Handler\TaskHandler;
use App\Repository\TaskRepository;
use App\Request\TaskRequest;
use App\Service\TaskService;
use App\Service\UserAssignmentProvider;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskHandlerTest extends TestCase
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SerializerInterface */
    private $serializer;

    /** @var TaskService */
    private $taskService;

    /** @var UserAssignmentProvider */
    private $provider;

    /** @var TaskHandler */
    private $handler;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->taskService = $this->createMock(TaskService::class);
        $this->provider = $this->createMock(UserAssignmentProvider::class);
        $this->handler = new TaskHandler(
            $this->serializer,
            $this->entityManager,
            $this->taskService,
            $this->provider
        );
    }

    public function testGetList(): void
    {
        $taskRepository = $this->createMock(TaskRepository::class);
        $tasks = [new Task()];

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(Task::class))
            ->willReturn($taskRepository);

        $taskRepository->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['parent' => null]))
            ->willReturn($tasks);

        $usersModels = [
            new UserModel(
                1,
                'John',
                'Doe',
                'john@doe.com',
                new DateTime(),
                new DateTime()
            )
        ];

        $context = SerializationContext::create()->setGroups(['list', 'Default']);

        $this->provider->expects($this->once())
            ->method('getUsersAssigmentModels')
            ->with($this->equalTo($tasks))
            ->willReturn($usersModels);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo($usersModels), $this->equalTo('json'), $this->equalTo($context))
            ->willReturn('');

        $expected = new Response('', Response::HTTP_OK);
        $this->assertEquals($expected, $this->handler->getList());
    }

    public function testCreateTask(): void
    {
        $content = '{"user_id":1, "title":"Task 1", "points":1, "is_done":1}';
        $request = new Request([], [], [], [], [], [], $content);
        $taskRequest = new TaskRequest($request);
        $this->taskService->expects($this->once())
            ->method('validate')
            ->with($taskRequest);

        $this->taskService->expects($this->once())
            ->method('saveTask')
            ->with($this->equalTo($taskRequest))
            ->willReturn('');

        $expected = new Response('', Response::HTTP_CREATED);
        $this->assertEquals($expected, $this->handler->create($request));
    }

    public function testUpdateTask(): void
    {
        $content = '{"user_id":1, "title":"Task 1", "points":1, "is_done":1}';
        $request = new Request([], [], [], [], [], [], $content);
        $taskRequest = new TaskRequest($request);
        $this->taskService->expects($this->once())
            ->method('validate')
            ->with($taskRequest);

        $task = new Task();
        $task->setTitle('Task 1')
            ->setPoints(1)
            ->setIsDone(true)
            ->setUserId(1)
            ->setLevel(1)
            ->setParent(null);

        $this->taskService->expects($this->once())
            ->method('saveTask')
            ->with($this->equalTo($taskRequest), $this->equalTo($task))
            ->willReturn('');

        $expected = new Response('', Response::HTTP_CREATED);
        $this->assertEquals($expected, $this->handler->update($request, $task));
    }
}
