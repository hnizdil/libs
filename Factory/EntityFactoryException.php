<?php

namespace Hnizdil\Factory;

use Exception;

class EntityFactoryException
	extends Exception
{

	const DOES_NOT_EXIST = 1;

	public static function doesNotExist($className) {

		$message = "Entity '{$className}' does not exist.";

		throw new self($message, self::DOES_NOT_EXIST);

	}

}
