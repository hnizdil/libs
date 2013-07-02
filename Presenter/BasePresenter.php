<?php

namespace Hnizdil\Presenter;

use Nette\Caching\Cache;
use Kdyby\Component\Headjs;
use Hnizdil\Nette\Forms\Controls;

abstract class BasePresenter
	extends \Nette\Application\UI\Presenter
{

	protected function startup() {

		parent::startup();

		Controls\DateInput::register();
		Controls\FileUpload::register();
		Controls\ImageUpload::register();
		Controls\MoneyInput::register();
		Controls\CheckboxList::register();
		Controls\OpeningHours::register();
		Controls\DependentSelectBox\DependentSelectBox::register();
		Controls\DependentSelectBox\JsonDependentSelectBox::register();
		\Kdyby\Forms\Containers\Replicator::register();

	}

	protected function beforeRender() {

		Controls\DependentSelectBox\JsonDependentSelectBox::tryJsonResponse($this);

	}

	public function addScript($path) {

		$this['headjs']->addScript($path);

	}

	public function addJQueryUiScript() {

		$this->addScript('jquery-ui-1.8.24.custom.min.js');

	}

	public function addAngularScript($path) {

		static $angularIncluded = FALSE;

		if (!$angularIncluded) {
			$this->addScript('lib/angular-1.0.0rc10.min.js');
			$this->addScript('lib/angular-locale_cs-cz.js');
			$angularIncluded = TRUE;
		}

		$this->addScript($path);

	}

	protected function createTemplate($class = NULL) {

		$template = parent::createTemplate($class);

		$template->registerHelper('json', 'json_encode');

		$template->shared = __DIR__ . '/../templates';
		$template->basePath = $this->context->parameters['basePath'];

		return $template;

	}

	protected function createComponentHeadjs($name) {

		$headjs = new Headjs($this, $name);
		$headjs->package = 'lib/' . Headjs::PACKAGE_LOAD;
		$headjs->javascriptDir = $this->context->parameters['basePath'] . 'js';

		$headjs->setCache(
			$this->context->nette->createCache('Component')->derive($name));

		$headjs->addScript('lib/jquery-1.7.1.min.js');
		$headjs->addScript('netteForms.js');

		return $headjs;

	}

	public function actionClearCache() {

		$this->context->cacheStorage->clean(array(Cache::ALL => true));

		$this->terminate();

	}

}
