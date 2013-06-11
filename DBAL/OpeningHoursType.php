<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Types\ObjectType;

class OpeningHoursType
	extends ObjectType
{

    public function getName() {

		return 'openingHours';

	}

}
