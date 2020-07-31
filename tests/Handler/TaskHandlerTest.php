<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DTO\UserModel;
use App\Entity\Task;
use App\Handler\TaskHandler;
use App\Request\TaskRequest;
use App\Service\UserTasksProvider;
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

    /** @var ValidatorInterface */
    private $validator;

    /** @var UserTasksProvider */
    private $provider;

    /** @var TaskHandler */
    private $handler;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->provider = $this->createMock(UserTasksProvider::class);
        $this->handler = new TaskHandler(
            $this->serializer,
            $this->entityManager,
            $this->validator,
            $this->provider
        );
    }

    public function testGetList(): void
    {
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
            ->method('getUsersTasksModels')
            ->willReturn($usersModels);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo($usersModels), $this->equalTo('json'), $this->equalTo($context))
            ->willReturn('');

        $expected = new Response('', Response::HTTP_OK);
        $this->assertEquals($expected, $this->handler->getList());
    }

    public function testCreateAndUpdateTask(): void
    {
        $content = '{"user_id":1, "title":"Task 1", "points":1, "is_done":1}';
        $request = new Request([], [], [], [], [], [], $content);
        $taskRequest = new TaskRequest($request);
        $violationList = $this->createMock(ConstraintViolationListInterface::class);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($taskRequest)
            ->willReturn($violationList);

        $task = new Task();
        $task->setTitle('Task 1')
            ->setPoints(1)
            ->setIsDone(true)
            ->setUserId(1)
            ->setLevel(1)
            ->setParent(null);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($task);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $context = SerializationContext::create()->setGroups(['Default']);
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo($task), $this->equalTo('json'), $this->equalTo($context))
            ->willReturn('');

        $expected = new Response('', Response::HTTP_CREATED);
        $this->assertEquals($expected, $this->handler->create($request));
    }
}
