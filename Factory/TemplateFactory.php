<?php

namespace Hnizdil\Factory;

use Nette\Templating\ITemplate;
use Nette\Application\UI\Presenter;
use Hnizdil\Factory\TemplateFactoryException as e;

/**
 * Továrna vytváří šablony. Pokud cesta nezačíná na "/",
 * vezme se šablona z adresáře se šablonami.
 */
class TemplateFactory
{

	private $template;
	private $templateDir;

	public function __construct(ITemplate $template, $templateDir) {

		$this->template    = $template;
		$this->templateDir = $templateDir;

	}

	public function create($path = NULL, Presenter $presenter = NULL) {

		$template = clone $this->template;

		if ($path) {

			if (substr($path, 0, 1) !== '/') {
				if ($presenter) {
					$paths = $presenter->formatTemplateFiles();
					$path = preg_replace('~[^/]+\.phtml~', $path, $paths[2]);
				}
				else {
					$path = "{$this->templateDir}/{$path}";
				}
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
