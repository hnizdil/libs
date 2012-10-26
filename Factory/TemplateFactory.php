<?php

namespace Hnizdil\Factory;

use Nette\Application\IPresenter;

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
		           $appDir,
		           $templateDir
	) {

		$this->presenter   = $presenter;
		$this->appDir      = $appDir;
		$this->templateDir = $templateDir;

	}

	public function create($template, $relativePath) {

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
				throw new TemplateException();
			}

		}

		return $template;

	}

}
