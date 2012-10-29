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
