<?php

namespace Hnizdil\Nette\Localization;

use Nette\Localization\ITranslator;

/**
 * Does no translation.
 */
class NoTranslator
	implements ITranslator
{

	public function translate($message, $count = 1) {

		$args = func_get_args();
		array_shift($args);

		return vsprintf($message, $args);

	}
	
}
