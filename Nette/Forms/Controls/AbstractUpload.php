<?php

namespace Hnizdil\Nette\Forms\Controls;

use Nette\Utils\Html;
use Nette\Http\FileUpload;
use Nette\Forms\Controls\UploadControl;

abstract class AbstractUpload
	extends UploadControl
{

	abstract function getThumbnail();

	public function setValue($value) {

		if (is_array($value)
			&& isset($value['file'])
			&& $value['file'] instanceof FileUpload
		) {
			$value['remove'] = (bool) @$value['remove'];
			$this->value = $value;
		}
		else {
			$this->value = array(
				'remove' => false,
				'file'   => new FileUpload(null),
			);
		}

	}

	protected function getFileControl() {

		$control = clone parent::getControl();
		$control->id .= '-file';
		$control->name .= '[file]';

		return $control;

	}

	protected function getRemoveControl() {

		$control = clone parent::getControl();
		$control->id .= '-remove';
		$control->name .= '[remove]';
		$control->type = 'hidden';

		$span = Html::el('span')
			->setText('×')
			->title($this->form->getTranslator()->translate('Odstranit'))
			->{'data-remove-input-id'}($control->id);

		return Html::el()->add($span)->add($control);

	}

	protected function getRemoveSpan() {

	}

	public function getControl() {

		$html = Html::el()
			->add($this->getFileControl());

		if (is_file($this->getOption('filePath'))) {
			$html->add(
				Html::el('span')
					->class('upload-thumbnail')
					->add($this->getThumbnail())
					->add($this->getRemoveControl()));
		}

		return $html;

	}

}
