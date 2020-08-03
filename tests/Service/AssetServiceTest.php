<?php

namespace App\Tests\Service;

use App\Client\CryptoClient;
use App\DTO\UserTotalAssetsModel;
use App\DTO\ValueAssetModel;
use App\Entity\Asset;
use App\Entity\Task;
use App\Request\AssetRequest;
use App\Request\TaskRequest;
use App\Service\AssetService;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AssetServiceTest extends TestCase
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

    /**
     * @var TaskService
     */
    private $taskService;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->taskService = new TaskService(
            $this->serializer,
            $this->entityManager,
            $this->validator
        );
    }

    public function testValidate()
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violationList = new ConstraintViolationList([$violation]);
        $request = new Request();
        $taskRequest = new TaskRequest($request);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($taskRequest))
            ->willReturn($violationList);

        $violation->expects($this->once())
            ->method('getPropertyPath')
            ->willReturn('path');

        $violation->expects($this->once())
            ->method('getMessage')
            ->willReturn('error message');

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo(['path' => ['message' => 'error message']]), $this->equalTo('json'))
            ->willReturn('');

        $this->expectException(Exception::class);

        $this->taskService->validate($taskRequest);
    }

    public function testSaveTask()
    {
        $taskRequest = $this->createMock(TaskRequest::class);

        $taskRequest->expects($this->once())
            ->method('getTitle')
            ->willReturn('Task 1');
        $taskRequest->expects($this->once())
            ->method('getPoints')
            ->willReturn(1);
        $taskRequest->expects($this->once())
            ->method('isDone')
            ->willReturn(true);
        $taskRequest->expects($this->once())
            ->method('isDone')
            ->willReturn(true);
        $taskRequest->expects($this->once())
            ->method('getParentId')
            ->willReturn(null);

        $task = new Task();
        $task->setTitle('Task 1')
            ->setPoints(1)
            ->setIsDone(true)
            ->setUserId(1)
            ->setLevel(1)
            ->setParent(null);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($task));
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo($task), $this->equalTo('json'))
            ->willReturn('');

        $this->assertEquals('', $this->taskService->saveTask($taskRequest, $task));
    }
}
