<?php

namespace Hnizdil\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Tváří se jako ENUM, ale je to INTEGER s namapovanými hodnotami.
 */
abstract class EnumType
	extends IntegerType
{

	/**
	 * Povolené hodnoty.
	 */
	protected $values = array();

	public function convertToPHPValue($value, AbstractPlatform $platform) {

		if ($value === NULL || $value === '') {
			return $value;
		}

		$value = (int) $value;

		if (!array_key_exists($value, $this->values)) {
			ConversionException::conversionFailed($value, __CLASS__);
		}

		return $this->values[$value];

	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform) {

		if (($key = array_search($value, $this->values)) === FALSE) {
			ConversionException::conversionFailed($value, __CLASS__);
		}

		return $key;

	}

	public function getValues() {

		return $this->values;

	}

	public function getSelectItems() {

		return array_combine($this->values, $this->values);

	}

	public static function revert($constant) {

		static $instance = NULL;
		static $reverted = array();

		if (!$instance) {
			$instance = Type::getType(static::NAME);
		}

		if (!$reverted) {
			$reverted = array_flip($instance->getValues());
		}

		return $reverted[constant(get_class($instance) . '::' . $constant)];

	}

}
