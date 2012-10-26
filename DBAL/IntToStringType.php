<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform,
	Doctrine\DBAL\Types\ConversionException,
	Doctrine\DBAL\Types\IntegerType;

abstract class IntToStringType
	extends IntegerType
{

	/**
	 * Mapování klíče na hodnotu
	 *
	 * @var array
	 * @access protected
	 */
	protected $map = array();

	public function convertToPHPValue($value, AbstractPlatform $platform) {

		if (!array_key_exists($value, $this->map)) {
			ConversionException::conversionFailed($value, __CLASS__);
		}

		return $this->map[(int)$value];

	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform) {

		if (($key = array_search($value, $this->map)) === FALSE) {
			ConversionException::conversionFailed($value, __CLASS__);
		}

		return $key;

	}

	public function getSelectItems() {

		return array_combine($this->map, $this->map);

	}

}
