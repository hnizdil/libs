<?php

namespace Hnizdil\Service;

use Exception;

class WwwPathGetterException
	extends Exception
{

	const NOT_IN_WWW = 1;

	public static function notInWww($path) {

		$message = "Path '{$path}' not in WWW directory.";

		throw new self($message, self::NOT_IN_WWW);

	}

}
