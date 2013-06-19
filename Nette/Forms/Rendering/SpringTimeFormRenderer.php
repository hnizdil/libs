<?php

namespace Hnizdil\Nette\Forms\Rendering;

use Nette\Forms\IControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Rendering\DefaultFormRenderer;

class SpringTimeFormRenderer
	extends DefaultFormRenderer
{

	private $buttons = array();

	public function __construct() {

		$this->wrappers['form']['container']       = 'div class=form';
		$this->wrappers['error']['container']      = 'div class="msg msg-error"';
		$this->wrappers['error']['item']           = 'p';
		$this->wrappers['controls']['container']   = null;
		$this->wrappers['pair']['container']       = 'p';
		$this->wrappers['label']['requiredsuffix'] = ' <span>(povinné)</span>';

	}

	public function renderControl(IControl $control) {

		$control->getControlPrototype()->class[] = 'field';
		$control->getControlPrototype()->class[] = 'size1';

		return parent::renderControl($control);

	}

	public function renderBody() {

		foreach ($this->form->getControls() as $control) {
			if (!$control->getOption('rendered') && $control instanceof Button) {
				$this->buttons[] = $control;
				$control->setOption('rendered', true);
			}
		}

		return parent::renderBody();

	}

	public function renderEnd() {

		$s = '<div class="buttons">';

		foreach ($this->buttons as $button) {
			$s .= $button->getControl();
		}

		$s .= '</div>';

		return $s . parent::renderEnd();

	}

}
