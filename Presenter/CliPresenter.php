<?php

namespace Hnizdil\Presenter;

use Nette\Http\UrlScript;
use Nette\Http\Request;
use Nette\Application\UI\Presenter;

/**
 * Zajišťuje správné absolutní adresy generované z CLI.
 */
class CliPresenter
	extends Presenter
{

	protected function getHttpRequest() {

		return new Request(
			new UrlScript($this->context->parameters['baseUri']));

	}

}
