<?php

namespace Hnizdil\ORM;

use Exception;
use Hnizdil\ORM\AbstractEntityException as e;

abstract class AbstractEntity
{

	public function __get($name) {

		if (property_exists($this, $name)) {
			return $this->$name;
		}
		elseif (method_exists($this, $method = 'get' . $name)) {
			return $this->$method();
		}
		else {
			$fullName = get_class($this) . '::$' . $name;
			throw new \Exception("Property '{$fullName}' does not exist");
		}

	}

	public function __isset($name) {

		return property_exists($this, $name);

	}

	public function isFresh() {

		if (property_exists($this, 'id')) {
			return $this->id === NULL;
		}
		else {
			e::missingIdField(__FUNCTION__, $this);
		}

	}

	public function setNullableString($property, $value) {

		if (!property_exists($this, $property)) {
			e::missingProperty($this, $property);
		}

		$this->$property = $value === '' ? NULL : (string)$value;

	}

	public function setNullableInteger($property, $value) {

		if (!property_exists($this, $property)) {
			e::missingProperty($this, $property);
		}

		$this->$property = ctype_digit($value) ? (int)$value : NULL;

	}

}

class AbstractEntityException
	extends Exception
{

	const MISSING_ID_FIELD = 1;

	const MISSING_PROPERTY = 2;

	public static function missingIdField($method, $entity) {

		$className = get_class($entity);
		$message = "Entity '{$className}' is missing ID field. "
			. "Method '{$method}' should probably be overloaded.";

		throw new self($message, self::MISSING_ID_FIELD);

	}

	public static function missingProperty($entity, $property) {

		$className = get_class($entity);
		$message = "Entity '{$className}' has no property '{$property}'.";

		throw new self($message, self::MISSING_PROPERTY);

	}

}
