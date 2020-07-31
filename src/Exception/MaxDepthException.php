<?php

declare(strict_types=1);

namespace App\Exception;

class MaxDepthException extends \Exception
{
    public function __construct()
    {
        parent::__construct('{"message": "Max sub tasks depth is reached"}');
    }
}