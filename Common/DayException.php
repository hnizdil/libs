<?php

namespace Hnizdil\Common;

use Exception;

class DayException
	extends Exception
{

	const UNKNOWN_DAY = 1;

	public static function unknownDay($day) {

		throw new self("Unknown day '{$day}'", self::UNKNOWN_DAY);

	}

}
