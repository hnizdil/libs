<?php

namespace Hnizdil\ORM;

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
			e::missingProperty($this, $name);
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

	protected function setNullableString($property, $value) {

		$this->checkIfPropertyExists($property);

		$this->$property = $value === '' ? NULL : (string)$value;

	}

	protected function setNullableInteger($property, $value) {

		$this->checkIfPropertyExists($property);

		$this->$property = ctype_digit("{$value}") ? (int)$value : NULL;

	}

	protected function addIfNotContained($property, $entity) {

		$this->checkIfPropertyExists($property);

		if (!$this->$property->contains($entity)) {
			$this->$property->add($entity);
		}

	}

	private function checkIfPropertyExists($property) {

		if (!property_exists($this, $property)) {
			e::missingProperty($this, $property);
		}

	}

}
