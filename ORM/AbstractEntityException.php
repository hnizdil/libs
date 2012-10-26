<?php

namespace Hnizdil\ORM;

use Exception;

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
