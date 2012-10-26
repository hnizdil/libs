<?php

namespace Hnizdil\Gridito;

class DoctrineQueryBuilderModel
	extends \Gridito\Model\DoctrineQueryBuilderModel
{

	protected $entityClassName;

	public function getQueryBuilder() {

		return $this->qb;

	}

	public function getItemValue($item, $valueName) {

		if (isset($this->columnAliases[$valueName])) {
			$getterPath = $this->columnAliases[$valueName]->getterPath;
		}
		else {
			$getterPath = $valueName;
		}

		$getters = explode('.', $getterPath);

		$value = $item;

		foreach ($getters as $getter) {
			$value = $value->$getter;
		}

		return $value;
	}

	public function hasColumnAlias($columnName) {

		return array_key_exists($columnName, $this->columnAliases);

	}

	public function setEntityClassName($entityClassName) {

		$this->entityClassName = $entityClassName;

	}

}
