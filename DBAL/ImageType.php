<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Types\StringType;

class ImageType
	extends StringType
{

	public function getName() {

		return 'image';

	}

}
