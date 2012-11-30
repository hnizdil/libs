<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform,
	Doctrine\DBAL\Types\DecimalType,
	Hnizdil\Money;

class MoneyType
	extends DecimalType
{

	protected $values = array();

	public function getSqlDeclaration(
		array $fieldDeclaration,
		AbstractPlatform $platform
	) {

		$fieldDeclaration['precision'] = 10;
		$fieldDeclaration['scale']     = 2;

		return $platform->getDecimalTypeDeclarationSQL($fieldDeclaration);

	}

	public function convertToPHPValue($value, AbstractPlatform $platform) {

		return new Money($value);

	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform) {

		$amount = NULL;

		if ($value instanceof Money) {
			$amount = $value->getAmount();
		}

		if (!is_numeric($amount)) {
			$amount = NULL;
		}

		return $amount;

	}

	public function getName() {

		return 'money';

	}

}
