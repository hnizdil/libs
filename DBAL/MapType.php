<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;

abstract class MapType
	extends StringType
{

	/**
	 * Mapování klíče na hodnotu
	 *
	 * @var array
	 * @access protected
	 */
	protected $map = array();

	public function convertToPHPValue($value, AbstractPlatform $platform) {

		if ($value === NULL || $value === '') {
			return $value;
		}

		if (!array_key_exists($value, $this->map)) {
			ConversionException::conversionFailed($value, __CLASS__);
		}

		return $this->map[$value];

	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform) {

		if (($key = array_search($value, $this->map)) === FALSE) {
			ConversionException::conversionFailed($value, __CLASS__);
		}

		return $key;

	}

	public function getMap() {

		return $this->map;

	}

	public function getSelectItems() {

		return array_combine($this->map, $this->map);

	}

}
