<?php

declare(strict_types=1);

namespace App\DTO;

use JMS\Serializer\Annotation\Type;

class UsersDataModel
{
    /**
     * @var UserModel[]
     * @Type("array<App\DTO\UserModel>")
     */
    private $data;

    public function __construct(UserModel $data)
    {
        $this->data = $data;
    }

    /**
     * @return UserModel[]
     */
    public function getData(): array
    {
        return $this->data;
    }
}
