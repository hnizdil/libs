<?php

namespace Hnizdil\Common;

use DateTime;
use Serializable;
use Hnizdil\Common\Day;
use Nette\Utils\Html;
use Nette\Application\UI\Control;

class OpeningHours
	extends Control
	implements Serializable
{

	protected $weekStartsOnSunday;

	protected $timeFormat = 'G:i';

	protected $days = array(
		Day::SUN => array(),
		Day::MON => array(),
		Day::TUE => array(),
		Day::WED => array(),
		Day::THU => array(),
		Day::FRI => array(),
		Day::SAT => array(),
	);

	protected $note;

	public function __construct($weekStartsOnSunday) {

		$this->weekStartsOnSunday = $weekStartsOnSunday;

	}

	public function setTimeFormat($timeFormat) {

		$this->timeFormat = $timeFormat;

	}

	public function setDay($day, DateTime $from, DateTime $to) {

		$this->days[$day]['time'] = array($from, $to);

	}

	public function getDays() {

		$days = $this->days;

		// add Day objects to days
		foreach ($this->days as $dayId => $_) {
			$days[$dayId]['day'] = new Day($dayId);
		}

		// move sunday to the end
		if (!$this->weekStartsOnSunday) {
			$sunday = $days[Day::SUN];
			unset($days[Day::SUN]);
			$days[Day::SUN] = $sunday;
		}

		return $days;

	}

	public function setNote($note) {

		$this->note = $note;

	}

	public function getNote() {

		return $this->note;

	}

	public function format(DateTime $time) {

		return $time->format($this->timeFormat);

	}

	/**
	 * Serializable interface
	 */

	public function serialize() {

		return serialize(array(
			$this->weekStartsOnSunday,
			$this->timeFormat,
			$this->days,
			$this->note,
		));

	}

	public function unserialize($data) {

		$data = unserialize($data);

		$this->weekStartsOnSunday = $data[0];
		$this->timeFormat         = $data[1];
		$this->days               = $data[2];
		$this->note               = $data[3];

	}

}
