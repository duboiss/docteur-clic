<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TimeConstraintsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint TimeConstraints */

        if (null === $value || '' === $value) {
            return;
        }

        $hour = (int) $value->format('H');
        $minute = (int) $value->format('i');

        if (0 !== $minute || $hour < 7 || $hour > 18) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ time }}', $value->format('H:i'))
                ->addViolation();
        }
    }
}
