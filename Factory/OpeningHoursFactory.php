<?php

namespace Hnizdil\Factory;

use Hnizdil\Common\OpeningHours;

class OpeningHoursFactory
{

	private $weekStartsOnSunday;

	public function __construct($weekStartsOnSunday) {

		$this->weekStartsOnSunday = $weekStartsOnSunday;

	}

	public function create() {

		return new OpeningHours($this->weekStartsOnSunday);

	}

}
