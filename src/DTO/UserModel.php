<?php

declare(strict_types=1);

namespace App\DTO;

use DateTime;
use JMS\Serializer\Annotation\Type;

class UserModel
{
    /**
     * @var int
     * @Type("int")
     */
    private $id;

    /**
     * @var string
     * @Type("string")
     */
    private $firstName;

    /**
     * @var string
     * @Type("string")
     */
    private $lastName;

    /**
     * @var string
     * @Type("string")
     */
    private $email;

    /**
     * @var DateTime
     * @Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $createdAt;

    /**
     * @var DateTime
     * @Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $updatedAt;

    public function __construct(
        int $id,
        string $firstName,
        string $lastName,
        string $email,
        DateTime $createdAt,
        DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
