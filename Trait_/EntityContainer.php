<?php

/* Dočasně zrušeno kvůli ShipitoSpace

namespace Hnizdil\Trait_;

use Hnizdil\ORM\AbstractEntity;

trait TEntityContainer
{

	private $entity;

	public function getEntity($onlyObject = FALSE) {

		if ($onlyObject) {
			return $this->entity instanceof AbstractEntity
				? $this->entity
				: NULL;
		}
		else {
			return $this->entity;
		}

	}

	public function setEntity($entity) {

		$this->entity = $entity;

	}

}

*/
