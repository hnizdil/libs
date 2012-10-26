<?php

namespace Hnizdil\Presenter;

use Nette\Diagnostics\Debugger;
use Nette\Application\UI\Presenter;
use Nette\Application\BadRequestException;

abstract class ShowExceptionPresenter
	extends Presenter
{

	public function renderDefault($exceptionFileName) {

		$path = realpath(Debugger::$logDirectory . '/' . $exceptionFileName);

		if ($path) {
			readfile($path);
			exit;
		}
		else {
			throw new BadRequestException;
		}

	}

}
