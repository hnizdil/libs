<?php

namespace Hnizdil\Nette\Forms;

// Dočasně zrušeno kvůli ShipitoSpace
//use Hnizdil\Trait_\TEntityContainer;

class EntityContainer
	extends \Nette\Forms\Container
{

	/* Dočasně zrušeno kvůli ShipitoSpace

	use TEntityContainer;

	*/

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
