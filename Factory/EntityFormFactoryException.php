<?php

namespace Hnizdil\Factory;

use Exception;

class EntityFormFactoryException
	extends Exception
{

	const ASSOCIATED_ENTITY_NOT_FOUND = 1;

	const NO_ASSOCIATION = 2;

	const ENTITIES_NOT_ASSOCIATED = 3;

	const NO_TO_MANY_SETTER = 4;

	const NO_TO_MANY_UNSETTER = 5;

	public static function associatedEntityNotFound($className, $key) {

		$key = json_encode($key);

		$message = "Associated entity '{$class}' with key '{$key}' not found.";

		throw new self($message, self::ASSOCIATED_ENTITY_NOT_FOUND);

	}

	public static function noAssociation($className, $field) {

		$message = "No association on {$className}::{$field}.";

		throw new self($message, self::NO_ASSOCIATION);

	}

	public static function entitiesNotAssociated($className1, $className2) {

		$message = "Entities '{$className1}' and '{$className2}' "
			. ' are not associated.';

		throw new self($message, self::ENTITIES_NOT_ASSOCIATED);

	}

	public static function noToManySetter($className, $field) {

		$message = "ToMany association {$className}::{$field} has no setter.";

		throw new self($message, self::NO_TO_MANY_SETTER);

	}

	public static function noToManyUnsetter($className, $field) {

		$message = "ToMany association {$className}::{$field} has no unsetter.";

		throw new self($message, self::NO_TO_MANY_UNSETTER);

	}

}
