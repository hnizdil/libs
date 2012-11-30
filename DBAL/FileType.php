<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Types\StringType;

class FileType
	extends StringType
{

	public function getName() {

		return 'file';

	}

}
