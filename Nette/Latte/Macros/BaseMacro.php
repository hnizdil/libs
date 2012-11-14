<?php

namespace Hnizdil\Nette\Latte\Macros;

use Nette\Latte\IMacro;
use Nette\Latte\MacroNode;

abstract class BaseMacro
	implements IMacro
{

	protected function nodeCanBeEmpty(MacroNode $node) {

		$args = $node->args;

		$node->isEmpty = substr($args, -1) === '/';

		if ($node->isEmpty) {

			// odstranění uzavírací lomítka
			$args = substr($args, 0, -1);

			// odstranění případné mezery na konci, která kazí escapování
			$args = rtrim($args);

			$node->setArgs($args);

		}

	}

	public function initialize() {

	}

	public function finalize() {

	}

}
