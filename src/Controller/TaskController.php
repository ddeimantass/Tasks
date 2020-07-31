<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Task;
use App\Handler\TaskHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/task")
 */
class TaskController extends AbstractController
{
    /** @var TaskHandler */
    private $taskHandler;

    public function __construct(TaskHandler $taskHandler)
    {
        $this->taskHandler = $taskHandler;
    }

    /**
     * @Route("", methods="GET")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->taskHandler->getList();
    }

    /**
     * @Route("", methods="POST")
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        return $this->taskHandler->create($request);
    }

    /**
     * @Route("/{id}", methods="PUT")
     * @param Request $request
     * @param Task $task
     *
     * @return Response
     */
    public function update(Request $request, Task $task): Response
    {
        return $this->taskHandler->update($request, $task);
    }
}
