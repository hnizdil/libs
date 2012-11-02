<?php

namespace Hnizdil\Factory;

use Nette\Application\IPresenter;
use Nette\Templating\ITemplate;
use Hnizdil\Factory\TemplateFactoryException as e;

/**
 * Továrna vytváří šablony ze zadané relativní cesty.
 */
class TemplateFactory
{

	private $presenter;
	private $appDir;
	private $templateDir;

	public function __construct(
		IPresenter $presenter,
		ITemplate  $template,
		           $appDir,
		           $templateDir
	) {

		$this->presenter   = $presenter;
		$this->template    = $template;
		$this->appDir      = $appDir;
		$this->templateDir = $templateDir;

	}

	public function create($relativePath) {

		$template = clone $this->template;

		if ($relativePath) {

			// partial
			if (substr($relativePath, 0, 1) == '_') {
				$presenterName = $this->presenter->getName();
				// no module
				if (($pos = mb_strrpos($presenterName, ':')) === FALSE) {
					$path = $this->templateDir
						. '/' . $presenterName
						. '/' . $relativePath;
				}
				// module
				else {
					// insert dir "templates" after last module
					$subPath = substr_replace(
						$presenterName, 'templates/', $pos + 1, 0);
					// replace module separator with "Module/"
					$subPath = str_replace(':', 'Module/', $subPath);
					// construct path
					$path = $this->appDir
						. '/' . $subPath
						. '/' . $relativePath;
				}
			}
			// template in templates dir
			else {
				$path = $this->templateDir . '/' . $relativePath;
			}

			if (is_readable($path)) {
				$template->setFile($path);
			}
			else {
				e::fileNotFound($path);
			}

		}

		return $template;

	}

}
