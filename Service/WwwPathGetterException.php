<?php

namespace Hnizdil\Service;

use Exception;

class WwwPathGetterException
	extends Exception
{

	const NOT_FOUND = 1;

	const NOT_IN_WWW = 2;

	public static function notFound($path) {

		throw new self("Path '{$path}' not found.", self::NOT_FOUND);

	}

	public static function notInWww($path) {

		$message = "Path '{$path}' not in WWW directory.";

		throw new self($message, self::NOT_IN_WWW);

	}

}
