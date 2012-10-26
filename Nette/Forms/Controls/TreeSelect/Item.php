<?php

namespace Hnizdil\Nette\Forms\Controls\TreeSelect;

class Item
	implements IItem
{

	private $added = FALSE;

	private $order = 0;

	private $parent = NULL;

	private $children = array();

	public function getId() {

		if ($this->parent) {
			return $this->parent->getId() . '-' . $this->order;
		}
		else {
			// has to be string (strict in_array() is used later)
			return '1';
		}

	}

	public function getAdded() {

		return $this->added;

	}

	public function setAdded($added) {

		$this->added = (bool) $added;

	}

	public function setOrder($order) {

		$this->order = $order;

	}

	public function setParent(IItem $parent) {

		$this->parent = $parent;

	}

	public function addChild(IItem $item) {

		$item->setParent($this);
		$item->setOrder(count($this->children) + 1);

		$this->children[] = $item;

	}

	public function hasChildren() {

		return !empty($this->children);

	}

	public function getChildren() {

		return $this->children;

	}

	public function getInTreeName() {

		throw new \RuntimeException(
			'Method ' . __METHOD__ . ' has to be overriden');

	}

	public function getAloneName() {

		throw new \RuntimeException(
			'Method ' . __METHOD__ . ' has to be overriden');

	}

}
