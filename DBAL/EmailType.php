<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Types\StringType;

class EmailType
	extends StringType
{

	public function getName() {

		return 'email';

	}

}
