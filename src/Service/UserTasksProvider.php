<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\UserAssignmentsModel;
use App\Entity\AssignmentInterface;
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
     * @param AssignmentInterface[]
     * @return UserAssignmentsModel[]
     */
    public function getUsersAssigmentModels(array $assignments): array
    {
        $usersDetails = $userTasksModels = [];
        foreach ($assignments as $assignment) {
            $this->setDetails($usersDetails, $assignment);
        }

        $users = $this->client->getUsers();
        foreach ($usersDetails as $id => $userDetails) {
            $fullName = isset($users[$id]) ? $users[$id]->getFullName() : 'Unknown';
            $userTasksModels[] = new UserAssignmentsModel(
                $fullName,
                $userDetails['totalPoints'],
                $userDetails['donePoints'],
                $userDetails['assignments']
            );
        }

        return $userTasksModels;
    }

    /**
     * @param array $usersDetails
     * @param AssignmentInterface $assignment
     */
    private function setDetails(array &$usersDetails, AssignmentInterface $assignment): void
    {
        if (isset($usersDetails[$assignment->getUserId()])) {
            $usersDetails[$assignment->getUserId()]['totalPoints'] += $assignment->getPoints();
            $usersDetails[$assignment->getUserId()]['donePoints'] += $assignment->getDonePoints();
        } else {
            $usersDetails[$assignment->getUserId()]['totalPoints'] = $assignment->getPoints();
            $usersDetails[$assignment->getUserId()]['donePoints'] = $assignment->getDonePoints();
        }

        $usersDetails[$assignment->getUserId()]['assignments'][] = $assignment;
    }
}