<?php

namespace Hnizdil\Service;

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
