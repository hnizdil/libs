<?php

namespace Hnizdil\Nette\Forms\Controls\TreeSelect;

use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;

class Control
	extends BaseControl
{

	protected $value = NULL;

	protected $showRootItem = TRUE;

	protected $rootItem;

	/**
	 * @param string $value JSON encoded array of item IDs
	 * @access public
	 * @return void
	 */
	public function setValue($value) {

		$this->value = json_decode($value, TRUE);

	}

	/**
	 * @access public
	 * @return string JSON encoded array of added item IDs
	 */
	public function getValue() {

		if (is_array($this->value)) {
			return json_encode($this->value);
		}
		else {
			return NULL;
		}

	}

	public function getAddedItems() {

		$this->updateAddedItemsByValue();

		return $this->getAddedItemsInternal();

	}

	public function setDefaultValue($value) {

		// ignored

		return $this;

	}

	public function getControl() {

		$this->updateAddedItemsByValue();

		$control = parent::getControl();

		$template = $this->form->presenter->context->nette->createTemplate();
		$template->setFile(__DIR__ . '/template.phtml');

		$template->control = $control;
		$template->item = $this->rootItem;
		$template->showRootItem = $this->showRootItem;

		return $template->__toString();

	}

	public static function register($method = 'addTreeSelect') {

		Container::extensionMethod($method,
			function(Container $container, $name, IItem $rootItem) {

				$treeSelectClass = __CLASS__;
				$treeSelect = new $treeSelectClass;

				$treeSelect->setRootItem($rootItem);

				return $container[$name] = $treeSelect;

			});

	}

	public function setRootItem(IItem $item) {

		$this->rootItem = $item;

	}

	public function setShowRootItem($showRootItem) {

		$this->showRootItem = $showRootItem;

	}

	protected function getAddedItemsInternal(IItem $item = NULL) {

		// use $rootItem by default
		if ($item === NULL) {
			$item = $this->rootItem;
		}

		$added = array();

		if ($item->getAdded()) {
			$added[] = $item;
		}

		foreach ($item->getChildren() as $child) {
			$added = array_merge($added, $this->getAddedItemsInternal($child));
		}

		return $added;

	}

	protected function updateAddedItemsByValue(IItem $item = NULL) {

		// no value was set
		if ($this->value === NULL) {
			return;
		}

		// use $rootItem by default
		if ($item === NULL) {
			$item = $this->rootItem;
		}

		$item->setAdded(in_array($item->getId(), $this->value, TRUE));

		foreach ($item->getChildren() as $child) {
			$this->updateAddedItemsByValue($child);
		}

	}

}
