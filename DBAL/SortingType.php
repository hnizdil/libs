<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

class SortingType
	extends IntegerType
{

	public function getSQLDeclaration(
		array $fieldDeclaration,
		AbstractPlatform $platform
	) {

		$fieldDeclaration['notnull']  = FALSE;
		$fieldDeclaration['unsigned'] = TRUE;

		return $platform->getIntegerTypeDeclarationSQL($fieldDeclaration);

	}

	public function getName() {

		return 'sorting';

	}

}
