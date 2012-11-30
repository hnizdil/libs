<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Types\StringType;

class UrlType
	extends StringType
{

	public function getName() {

		return 'url';

	}

}
