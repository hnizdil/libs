<?php

namespace Hnizdil\Doctrine\ORM\Mapping;

use Hnizdil\ORM\AbstractEntity;

/**
 * Přidává některá další metadata pro tvorbu gridů a formulářů.
 *
 * @author Jan Hnízdil <jan.hnizdil@dobryweb.cz>
 */
class ClassMetadata
	extends \Doctrine\ORM\Mapping\ClassMetadata
{

	public $nameFields = array();

	public $shortNameFields = array();

	public $formFields = array();

	public $gridFields = array();

	public $detailLinkFields = array();

	public $gridActions = array();

	public $gridMultiActions = array();

	public function getEntityName(AbstractEntity $entity) {

		$nameValues = array();

		foreach ($this->nameFields as $field) {
			$nameValues[] = $entity->$field;
		}

		return implode($nameValues, ' ');

	}

	public function getEntityShortName(AbstractEntity $entity) {

		$nameValues = array();

		foreach ($this->shortNameFields as $field) {
			$nameValues[] = $entity->$field;
		}

		return implode($nameValues, ' ');

	}

	/**
	 * {@inheritdoc}
	 */
	public function __sleep() {

		$serialized = parent::__sleep();

		$serialized[] = 'nameFields';
		$serialized[] = 'shortNameFields';
		$serialized[] = 'formFields';
		$serialized[] = 'gridFields';
		$serialized[] = 'detailLinkFields';
		$serialized[] = 'gridActions';
		$serialized[] = 'gridMultiActions';

		return $serialized;

	}

	public function addNameField($name) {

		$this->nameFields[] = $name;

	}

	public function addShortNameField($name) {

		$this->shortNameFields[] = $name;

	}

	public function addFormField($name, $attrs) {

		$this->formFields[$name] = $attrs;

	}

	public function addGridField($name, $attrs) {

		$this->gridFields[$name] = $attrs;

	}

	public function addDetailLinkField($name) {

		$this->detailLinkFields[] = $name;

	}

	public function setGridActions(array $actions) {

		$this->gridActions = $actions;

	}

	public function setGridMultiActions(array $actions) {

		$this->gridMultiActions = $actions;

	}

	public function setGridOrder(array $fields) {

		$this->gridFields = array_merge(array_flip($fields), $this->gridFields);

	}

	public function hasGridActionEdit() {

		return in_array('edit', $this->gridActions);

	}

	public function hasGridMultiActionDelete() {

		return in_array('delete', $this->gridMultiActions);

	}

}
