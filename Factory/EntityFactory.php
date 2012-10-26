<?php

namespace Hnizdil\Factory;

use Hnizdil\Factory\EntityFactoryException as e;

class EntityFactory
{

	public function create($className) {

		if (!class_exists($className)) {
			e::doesNotExist($className);
		}

		return new $className;

	}

}

class EntityFactoryException
	extends \Exception
{

	const DOES_NOT_EXIST = 1;

	public static function doesNotExist($className) {

		$message = "Entity '{$className}' does not exist.";

		throw new self($message, self::DOES_NOT_EXIST);

	}

}
