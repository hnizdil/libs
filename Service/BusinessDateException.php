<?php

namespace Hnizdil\Service;

use Exception;

class BusinessDateException
	extends Exception
{

	const NOT_FOUND_OR_NOT_READABLE = 1;

	public static function notFoundOrNotReadable($path) {

		$message = "File '{$path}' could not be found or is not readable.";

		throw new self($message, self::NOT_FOUND_OR_NOT_READABLE);

	}

}
