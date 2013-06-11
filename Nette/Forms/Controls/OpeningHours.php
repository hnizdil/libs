<?php

namespace Hnizdil\Nette\Forms\Controls;

use DateTime;
use Nette\Utils\Html;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Hnizdil\Factory\OpeningHoursFactory;
use Hnizdil\Common\Day;

class OpeningHours
	extends BaseControl
{

	protected $container;
	protected $dayContainer;
	protected $noteContainer;
	protected $timeSeparator;
	protected $openingHoursFactory;

	public function __construct($caption) {

		parent::__construct($caption);

		$this->container     = Html::el('span')->class('opening-hours');
		$this->dayContainer  = Html::el('span')->class('day');
		$this->noteContainer = Html::el('span')->class('note');
		$this->timeSeparator = Html::el('span')
			->class('separator')
			->setText('až');

	}

	public function getValue()
	{

		return $this->value;

	}

	public function setValue($value) {

		if ($value instanceof \Hnizdil\Common\OpeningHours) {
			$this->value = $value;
		}
		elseif (is_array($value)) {
			$this->initValue();
			foreach ($value['days'] as $dayId => $time) {
				if ($time['from'] && $time['to']) {
					$this->value->setDay(
						$dayId,
						new DateTime($time['from']),
						new DateTime($time['to'])
					);
				}
			}
			if ($value['note']) {
				$this->value->setNote($value['note']);
			}
		}
		else {
			$this->value = null;
		}

		return $this;

	}

	public function getControl()
	{

		if ($this->value === null) {
			$this->initValue();
		}

		$container = clone $this->container;

		foreach ($this->value->getDays() as $dayId => $day) {

			$controlFrom = parent::getControl();
			$controlFrom->name .= "[days][{$dayId}][from]";
			$controlFrom->id   .= "-{$dayId}-from";
			$this->setTimeDefault($controlFrom, @$day['time'][0]);

			$label = Html::el('label')
				->from($controlFrom->id)
				->setText($day['day']->getName());

			$controlTo = parent::getControl();
			$controlTo->name .= "[days][{$dayId}][to]";
			$controlTo->id   .= "-{$dayId}-to";
			$this->setTimeDefault($controlTo, @$day['time'][1]);

			$dayEl = clone $this->dayContainer;
			$dayEl->add($label);
			$dayEl->add($controlFrom);
			$dayEl->add(clone $this->timeSeparator);
			$dayEl->add($controlTo);

			$container->add($dayEl);

		}

		$noteControl = parent::getControl();
		$noteControl->name .= '[note]';
		$noteControl->id   .= '-note';
		$noteControl->setValue($this->value->getNote());

		$noteLabel = Html::el('label')
			->from($noteControl->id)
			->setText('Poznámka');

		$noteEl = clone $this->noteContainer;
		$noteEl->add($noteLabel);
		$noteEl->add($noteControl);

		$container->add($noteEl);

		return $container;

	}

	public function getLabel($caption = NULL)
	{

		$label = parent::getLabel($caption);
		$label->for = NULL;

		return $label;

	}

	public function setOpeningHoursFactory(OpeningHoursFactory $openingHoursFactory) {

		$this->openingHoursFactory = $openingHoursFactory;

	}

	public static function register()
	{

		Container::extensionMethod('addOpeningHours', function (Container $_this, $name, $openingHoursFactory, $label = null) {
			$control = new self($label);
			$control->setOpeningHoursFactory($openingHoursFactory);
			return $_this[$name] = $control;
		});

	}

	public function getContainerPrototype() {

		return $this->container;

	}

	public function getDayContainerPrototype() {

		return $this->dayContainer;

	}

	public function getNoteContainerPrototype() {

		return $this->noteContainer;

	}

	public function getTimeSeparatorPrototype() {

		return $this->timeSeparator;

	}

	protected function initValue() {

		$this->value = $this->openingHoursFactory->create();

	}

	protected function setTimeDefault($control, $time) {

		if ($time instanceof DateTime) {
			$control->setValue($this->value->format($time));
		}

	}

}
