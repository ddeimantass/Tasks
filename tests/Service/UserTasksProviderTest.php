<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\DTO\UserModel;
use App\DTO\UsersDataModel;
use App\DTO\UserAssignmentsModel;
use App\Entity\AssignmentInterface;
use App\Entity\Task;
use App\Handler\TaskHandler;
use App\Repository\TaskRepository;
use App\Request\TaskRequest;
use App\Service\UsersClient;
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

class UserTasksProviderTest extends TestCase
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UsersClient */
    private $client;

    /** @var UserTasksProvider */
    private $provider;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->client = $this->createMock(UsersClient::class);
        $this->provider = new UserTasksProvider($this->client, $this->entityManager);
    }

    public function testGetUsersAssigmentModels(): void
    {
        $assignment = $this->createMock(AssignmentInterface::class);
        $assignments = [$assignment];
        $userModel = new UserAssignmentsModel('Full Name', 0, 0, $assignments);
        $userModels = [$userModel];
        $user = $this->createMock(UserModel::class);
        $users = [$user];

        $this->client->expects($this->once())
            ->method('getUsers')
            ->willReturn($users);

        $user->expects($this->once())
            ->method('getFullName')
            ->willReturn('Full Name');

        $this->assertEquals($userModels, $this->provider->getUsersAssigmentModels($assignments));
    }
}
