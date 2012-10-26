<?php

namespace Hnizdil\Nette\Forms\Controls\TreeSelect;

interface IItem
{

	public function getId();

	public function getAdded();

	public function setAdded($added);

	public function setOrder($order);

	public function setParent(IItem $parent);

	public function addChild(IItem $item);

	public function hasChildren();

	public function getChildren();

	public function getInTreeName();

	public function getAloneName();

}
