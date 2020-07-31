<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\UserTasksModel;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

class UserTasksProvider
{
    /** @var UsersClient */
    private $client;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(UsersClient $client, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;
    }

    /**
     * @return UserTasksModel[]
     */
    public function getUsersTasksModels(): array
    {
        $usersDetails = $usersModels = [];
        $mainTasks = $this->entityManager->getRepository(Task::class)->findBy(['parent' => null]);

        /** @var Task $task */
        foreach ($mainTasks as $task) {
            $this->setDetails($usersDetails, $task);
        }

        $users = $this->client->getUsers();
        foreach ($usersDetails as $id => $userDetails) {
            $fullName = isset($users[$id]) ? $users[$id]->getFullName() : 'Unknown';
            $usersModels[] = new UserTasksModel(
                $fullName,
                $userDetails['totalPoints'],
                $userDetails['donePoints'],
                $userDetails['tasks']
            );
        }

        return $usersModels;
    }

    /**
     * @param array $usersDetails
     * @param Task $task
     */
    private function setDetails(array &$usersDetails, Task $task): void
    {
        if (isset($usersDetails[$task->getUserId()])) {
            $usersDetails[$task->getUserId()]['totalPoints'] += $task->getPoints();
            $usersDetails[$task->getUserId()]['donePoints'] += $task->getDonePoints();
        } else {
            $usersDetails[$task->getUserId()]['totalPoints'] = $task->getPoints();
            $usersDetails[$task->getUserId()]['donePoints'] = $task->getDonePoints();
        }

        $usersDetails[$task->getUserId()]['tasks'][] = $task;
    }
}