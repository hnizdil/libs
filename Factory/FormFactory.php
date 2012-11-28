<?php

namespace Hnizdil\Factory;

use Nette\Application\UI\Form;

class FormFactory
{

	protected $translatorFactory;

	public function __construct(TranslatorFactory $translatorFactory) {

		$this->translatorFactory = $translatorFactory;

	}

	public function create($parent, $name) {

		$form = new Form($parent, $name);

		$form->translator = $this->translatorFactory->create();

		return $form;

	}

}
