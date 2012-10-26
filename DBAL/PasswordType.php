<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Types\StringType;

class PasswordType
	extends StringType
{

    /** @override */
    public function getName() {

        return 'password';

    }

}
