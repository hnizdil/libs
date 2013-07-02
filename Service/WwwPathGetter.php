<?php

namespace Hnizdil\Service;

use Nette\DI\Container;
use Nette\Utils\Strings;
use Hnizdil\Service\WwwPathGetterException as e;

class WwwPathGetter
{

	private $container;

	public function __construct(Container $container) {

		$this->container = $container;

	}

	public function get($path) {

		$absolutePath = realpath($path);

		// path does not exist
		if (!$absolutePath) {
			return null;
		}

		$wwwDir = $this->container->parameters['wwwDir'];

		// path is not in WWW dir
		if (mb_strpos($absolutePath, $wwwDir) !== 0) {
			e::notInWww($absolutePath);
		}

		$substrStart = mb_strlen($wwwDir);

		$basePath = rtrim($this->container->parameters['basePath'], '/');
		if ($basePath !== '' && Strings::endsWith($wwwDir, $basePath)) {
			$substrStart -= mb_strlen($basePath);
		}

		return mb_substr($absolutePath, $substrStart);

	}

}
