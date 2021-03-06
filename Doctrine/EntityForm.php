<?php

namespace Hnizdil\Doctrine;

use Hnizdil\Doctrine\EntityFormException as e;
use Hnizdil\ORM\AbstractEntity;
// Dočasně zrušeno kvůli ShipitoSpace
//use Hnizdil\Trait_\TEntityContainer;
use Nette\Application\UI\Form;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\SubmitButton;

class EntityForm
	extends Form
{

	/* Dočasně zrušeno kvůli ShipitoSpace

	use TEntityContainer;

	*/

	private $entity;

	protected function attached($presenter) {

		parent::attached($presenter);

		$this->postAttached($this, $presenter);

	}

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

	private $callbacks = array(
		'postAttached'        => array(),
		'preFlush'            => array(),
		'postFlush'           => array(),
		'postDelete'          => array(),
		'prePersist'          => array(),
		'postContainer'       => array(),
		'virtualField'        => array(),
		'notUniqueException'  => array(),
		'foreignKeyException' => array(),
	);

	public function processData(SubmitButton $button) {

		$callback = reset($this['send']->onClick);

		callback($callback)->invoke($button);

	}

	public function setCallbacks(array $callbacks) {

		foreach ($callbacks as $name => $callback) {
			$this->callbacks[$name] = callback($callback);
		}

	}

	public function __call($method, $args) {

		// zavolání callbacku
		if ($callback = @$this->callbacks[$method]) {
			if ($callback->isCallable()) {
				return $callback->invokeArgs($args);
			}
			else {
				e::callbackNotCallable($method);
			}
		}
		// zavoláme rodiče
		elseif ($callback === NULL) {
			return parent::__call($method, $args);
		}
		// callback nebyl nastaven
		else {
		}

	}

}
