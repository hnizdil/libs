<?php

namespace Hnizdil\Doctrine;

use Exception;

class EntityFormException
	extends Exception
{

	const CALLBACK_NOT_CALLABLE = 1;

	public static function callbackNotCallable($callbackName) {

		$message = "Callback '{$callbackName}' is not callable.";

		throw new self($message, self::CALLBACK_NOT_CALLABLE);

	}

}
