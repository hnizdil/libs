<?php

namespace Hnizdil\Common;

class Day
{

	const SUN = 1;
	const MON = 2;
	const TUE = 4;
	const WED = 8;
	const THU = 16;
	const FRI = 32;
	const SAT = 64;

	protected $day;

	protected $names = array(
		self::SUN => 'Neděle',
		self::MON => 'Pondělí',
		self::TUE => 'Úterý',
		self::WED => 'Středa',
		self::THU => 'Čtvrtek',
		self::FRI => 'Pátek',
		self::SAT => 'Sobota',
	);

	public function __construct($day) {

		if (!isset($this->names[$day])) {
			DayException::unknownDay($day);
		}

		$this->day = $day;

	}

	public function getName() {

		return $this->names[$this->day];

	}

}
