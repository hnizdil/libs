<?php

namespace Hnizdil\Doctrine\DBAL\Types;

use Hnizdil\DBAL\EnumType;

class SexTypeEnum
	extends EnumType
{

	const MALE   = 'Muž';
	const FEMALE = 'Žena';

	protected $values = array(
		0 => self::MALE,
		1 => self::FEMALE,
	);

	public function getName() {

		return 'sexTypeEnum';

	}

}
