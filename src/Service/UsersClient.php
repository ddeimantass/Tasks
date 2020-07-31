<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\UsersDataModel;
use JMS\Serializer\SerializerInterface;

class UsersClient
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getUsers(): array
    {
        /** @var UsersDataModel $usersData */
        $usersData = $this->serializer->deserialize($this->getJson(), UsersDataModel::class, 'json');

        $users = $usersData->getData();
        foreach ($users as $user) {
            $users[$user->getId()] = $user;
        }

        return $users;
    }

    public function getUsersIds(): array
    {
        $users = json_decode($this->getJson(), true);

        return array_column($users['data'], 'id');
    }

    private function getJson(): string
    {
        return file_get_contents('https://gitlab.iterato.lt/snippets/3/raw');
    }
}