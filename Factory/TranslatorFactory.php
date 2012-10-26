<?php

namespace Hnizdil\Factory;

use Hnizdil\Nette\Localization\GettextTranslator;

class TranslatorFactory
{

	private $localesPath;

	public function __construct($localesPath) {

		$this->localesPath = $localesPath;

	}

	public function create($locale = '') {

		if (!$locale) {
			$locale = setlocale(LC_ALL, 0);
		}

		$localePath = "{$this->localesPath}/{$locale}.mo";

		if (!is_file($localePath)) {
			throw new \InvalidArgumentException(
				"Locale file '{$localePath}' not found");
		}

		return new GettextTranslator($localePath, $locale);

	}

}
