<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\UserTasksModel;
use App\Entity\Task;

class UserTasksParser
{
    /**
     * @param Task[] $mainTasks
     * @return UserTasksModel[]
     */
    public function getUserDTO(array $mainTasks): array
    {
        $usersDetails = [];
        foreach ($mainTasks as $task) {
            $usersDetails[$task->getUserId()]['tasks'] = $task;
            $usersDetails[$task->getUserId()]['totalPoints'] += $task->getPoints();
            $usersDetails[$task->getUserId()]['donePoints'] += $task->getDonePoints();
        }

        $usersModels = [];
        foreach ($usersDetails as $id => $userDetails) {
            $usersModels[] = new UserTasksModel(
                $id,
                $userDetails['totalPoints'],
                $userDetails['donePoints'],
                $userDetails['tasks']
            );
        }

        return $usersModels;
    }
}