<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TimeConstraints extends Constraint
{
    public string $message = 'L\'heure "{{ time }}" n\'est pas valide. Il doit s\'agit d\'une heure ronde comprise entre 7h00 et 18h00.';
}
