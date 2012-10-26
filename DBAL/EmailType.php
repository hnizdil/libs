<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Types\StringType;

class EmailType
	extends StringType
{

    /** @override */
    public function getName() {

        return 'email';

    }

}
