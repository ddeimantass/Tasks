<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Service\UsersClient;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UserExistsValidator extends ConstraintValidator
{
    /**
     * @var UsersClient
     */
    private $client;

    public function __construct(UsersClient $client)
    {
        $this->client = $client;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UserExists) {
            throw new UnexpectedTypeException($constraint, UserExists::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_int($value)) {
            throw new UnexpectedValueException($value, 'int');
        }

        $usersIds = $this->client->getUsersIds();

        if (!in_array($value, $usersIds)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}