<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform,
	Doctrine\DBAL\Types\ConversionException,
	Doctrine\DBAL\Types\Type;

abstract class EnumType
	extends Type
{

	protected $values = array();

	public function getSqlDeclaration(
		array $fieldDeclaration,
		AbstractPlatform $platform
	) {

		return "ENUM('" . implode("','", $this->values) . "')";

	}

	public function convertToPHPValue($value, AbstractPlatform $platform) {

		return $value;

	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform) {

		if ($value === '') {
			$value = NULL;
		}

		if ($value !== NULL && !in_array($value, $this->values)) {
			throw new \InvalidArgumentException("Invalid value '{$value}'");
			ConversionException::conversionFailed($value, __CLASS__);
		}

		return $value;

	}

	public function getValues() {

		return $this->values;

	}

	public function getSelectItems() {

		return array_combine($this->values, $this->values);

	}

	public function getName() {
	}

}
