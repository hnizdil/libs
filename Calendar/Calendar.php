<?php

namespace Hnizdil\Calendar;

use Nette\Application\UI\Control,
	Nette\ComponentModel\IContainer;

class Calendar
	extends Control
{

	/** @persistent */
	public $shift = 0;

	private $eventsCallback;

	private $currentDate;

	private $events = array();

	public function __construct(IContainer $parent = NULL, $name = NULL) {

		parent::__construct($parent, $name);

		$this->currentDate = new \DateTime();

	}

	public function loadState(array $params) {

		parent::loadState($params);

		if (is_int($this->shift) && $this->shift != 0) {
			$this->currentDate->modify($this->shift . ' months');
		}

	}

	public function render() {

		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/calendar.phtml');
		// TODO: nefunguje pro ceske znaky
		$template->registerHelper('ucfirst', function($text) {
			return ucfirst($text);
		});

		if (is_callable($this->eventsCallback)) {
			call_user_func($this->eventsCallback, $this);
		}

		$template->events = $this->events;

		$template->currentDate = clone $this->currentDate;

		$template->render();

	}

	public function addEvent($date, $event) {

		$day = $date->format('j');

		if (array_key_exists($day, $this->events)) {
			$this->events[$day][] = $event;
		}
		else {
			$this->events[$day] = array($event);
		}

	}

	public function handlePrev() {

		$this->shift--;

		$this->redirect('this');

	}

	public function handleNext() {

		$this->shift++;

		$this->redirect('this');

	}

	public function setEventsCallback($eventsCallback) {

		$this->eventsCallback = $eventsCallback;

	}

	public function setCurrentDate(\DateTime $currentDate) {

		$this->currentDate = $currentDate;

	}

	public function getCurrentDate() {

		return $this->currentDate;

	}

}
