<?php

namespace Hnizdil\Factory;

use Exception;

class TemplateFactoryException
	extends Exception
{

	const FILE_NOT_FOUND = 1;

	public static function fileNotFound($path) {

		$message = "Template file '{$path}' not found";

		throw new self($message, self::FILE_NOT_FOUND);

	}

}
