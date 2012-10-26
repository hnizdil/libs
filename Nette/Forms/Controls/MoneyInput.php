<?php

namespace Hnizdil\Nette\Forms\Controls;

use Hnizdil\Money;

class MoneyInput
	extends \Nette\Forms\Controls\BaseControl
{

	private $currency;

	public function __construct($caption = NULL, $currency = '') {

		parent::__construct($caption);

		$this->currency = $currency;

		$this->monitor('Nette\Application\UI\Presenter');

	}

	public function setValue($value) {

		if (!($value instanceof Money)) {
			$value = new Money(is_numeric($value) ? $value : NULL);
		}

		$this->value = $value;

		return $this;

	}

	public function getControl() {

		$control = parent::getControl();

		$control->type('text');

		if ($this->value instanceof Money
			&& is_numeric($this->value->getAmount())
		) {
			$control->value = $this->value->getAmount();
		}

		$control->data('moneyinput-currency', $this->currency);

		return $control;

	}

	public static function register() {

		$class = __CLASS__;

		\Nette\Forms\Container::extensionMethod('addMoney',
			function (\Nette\Forms\Container $form, $name, $caption, $currency)
			use ($class) {
				return $form[$name] = new $class($caption, $currency);
			});

	}

	public function setCurrency($currency) {

		$this->currency = $currency;

	}

	protected function attached($form) {

		parent::attached($form);

		if ($form instanceof \Nette\Application\UI\Presenter) {
			$form->presenter->addScript('jquery.formatCurrency-1.4.0.js');
			$form->presenter->addScript('jquery.formatCurrency.all.js');
			$form->presenter->addScript('jquery.formatCurrency.init.js');
		}

	}

}
