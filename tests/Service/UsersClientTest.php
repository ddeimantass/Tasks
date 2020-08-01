<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\DTO\UserModel;
use App\DTO\UsersDataModel;
use App\Service\UsersClient;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;

class UsersClientTest extends TestCase
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var UsersClient */
    private $client;

    public function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->client = new UsersClient($this->serializer);
    }

    public function testGetUsers(): void
    {
        $usersData = $this->createMock(UsersDataModel::class);
        $user = $this->createMock(UserModel::class);
        $users = [$user];
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($this->isType('string'), $this->equalTo(UsersDataModel::class), $this->equalTo('json'))
            ->willReturn($usersData);

        $usersData->expects($this->once())
            ->method('getData')
            ->willReturn($users);

        $user->expects($this->once())
            ->method('getId')
            ->willReturn(0);

        $this->assertEquals($users, $this->client->getUsers());
    }
}
