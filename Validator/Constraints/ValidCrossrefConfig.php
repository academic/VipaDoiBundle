<?php

namespace Ojs\OjsDoiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidCrossrefConfig extends Constraint
{
    public $username = 'username';

    public $password = 'password';

    public $prefix = 'prefix';

    public $usernameMessage = 'Kullanıcı adı bulunamadı.';

    public $passwordMessage = 'Parola bulunamadı.';

    public $prefixMessage = 'DOI ön ekiniz hatalı. Şunlardan biri olmalı : %s';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
