<?php

namespace Hnizdil\Factory;

use Hnizdil\Nette\Localization\NoTranslator;
use Hnizdil\Nette\Localization\GettextTranslator;

class TranslatorFactory
{

	private $localesPath;
	private $noTranslator;

	public function __construct($localesPath, NoTranslator $noTranslator) {

		$this->localesPath  = $localesPath;
		$this->noTranslator = $noTranslator;

	}

	public function create($locale = NULL) {

		if (!$locale) {
			$locale = setlocale(LC_ALL, 0);
		}

		$localePath = "{$this->localesPath}/{$locale}.mo";

		if (is_file($localePath)) {
			return new GettextTranslator($localePath, $locale);
		}
		else {
			return $this->noTranslator;
		}

	}

}
