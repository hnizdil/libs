<?php

namespace Hnizdil\Service;

use Exception;
use Hnizdil\Service\WwwPathGetterException as e;

class WwwPathGetter
{

	private $wwwDir;

	public function __construct($wwwDir) {

		$this->wwwDir = $wwwDir;

	}

	public function get($path) {

		$absolutePath = realpath($path);

		// path does not exist
		if (!$absolutePath) {
			e::notFound($path);
		}

		// path is not in WWW dir
		if (mb_strpos($absolutePath, $this->wwwDir) !== 0) {
			e::notInWww($absolutePath);
		}

		return mb_substr($absolutePath, mb_strlen($this->wwwDir));

	}

}

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
