<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use ApiTestCase\JsonApiTestCase;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends JsonApiTestCase
{
    private const URI = '/api/task';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function setUp(): void
    {
        $this->loadFixturesFromFile('tasks.yaml');
    }

    /**
     * @throws Exception
     */
    public function testList(): void
    {
        $this->loadFixturesFromFile('tasks.yaml');
        $this->client->request(Request::METHOD_GET, self::URI);
        $response = $this->client->getResponse();
        $this->assertResponse($response, 'taskList', Response::HTTP_OK);
    }

    /**
     * @throws Exception
     */
    public function testCreateSuccess(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URI,
            [],
            [],
            [],
            '{
                "user_id":1,
                "title":"Task 1",
                "points":3,
                "is_done":0,
                "email":"john.doe@email.com"
            }'
        );
        $response = $this->client->getResponse();

        $this->assertResponse($response, 'createTask', Response::HTTP_CREATED);
    }

    /**
     * @throws Exception
     */
    public function testCreateFail(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URI,
            [],
            [],
            [],
            '{
                "user_id":1,
                "title":"Task 1",
                "points":3
            }'
        );
        $response = $this->client->getResponse();

        $this->assertResponse($response, 'actionFail', Response::HTTP_BAD_REQUEST);
    }

    /**
     * @throws Exception
     */
    public function testUpdateSuccess(): void
    {
        $this->loadFixturesFromFile('tasks.yaml');
        /** @var Task $task */
        $task = $this->getEntityManager()->getRepository(Task::class)->findOneBy(['title' => 'Task 1']);
        $this->client->request(
            Request::METHOD_PUT,
            self::URI . '/' . $task->getId(),
            [],
            [],
            [],
            '{
                "user_id":2,
                "title":"Task 2",
                "points":4,
                "is_done":1,
                "email":"john.doe@email.com"
            }'
        );
        $this->getEntityManager()->refresh($task);
        $response = $this->client->getResponse();

        $this->assertResponse($response, 'updateTask', Response::HTTP_CREATED);
        $this->assertEquals($task->getTitle(), 'Task 2');
    }

    /**
     * @throws Exception
     */
    public function testUpdateFail(): void
    {
        $this->loadFixturesFromFile('tasks.yaml');
        /** @var Task $task */
        $task = $this->getEntityManager()->getRepository(Task::class)->findOneBy(['title' => 'Task 1']);
        $this->client->request(
            Request::METHOD_PUT,
            self::URI . '/' . $task->getId(),
            [],
            [],
            [],
            '{
                "user_id":2,
                "title":"Task 2",
                "points":4
            }'
        );
        $response = $this->client->getResponse();

        $this->assertResponse($response, 'actionFail', Response::HTTP_BAD_REQUEST);
    }
}
