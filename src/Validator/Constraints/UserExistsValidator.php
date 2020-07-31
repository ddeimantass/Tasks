<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UserExistsValidator extends ConstraintValidator
{
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

        $usersIds = $this->getUsersIds();

        if (!in_array($value, $usersIds)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function getUsersIds(): array
    {
        $usersJson = file_get_contents('https://gitlab.iterato.lt/snippets/3/raw');
        $users = json_decode($usersJson, true);

        return array_column($users['data'], 'id');
    }
}