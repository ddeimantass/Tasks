<?php

declare(strict_types=1);

namespace App\Entity;

interface AssignmentInterface
{
    public function getUserId(): int;

    public function getPoints(): int;

    public function getDonePoints(): int;
}