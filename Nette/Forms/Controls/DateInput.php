<?php

namespace Hnizdil\Nette\Forms\Controls;

use DateTime;
use Nette\Forms\Container;
use Nette\Forms\Form;
use Nette\Forms\Rules;
use Nette\Application\UI\Presenter;
use Vodacek\Forms\Controls\DateInput\DateInput as ParentDateInput;

class DateInput
	extends ParentDateInput
{

	public static function register() {

		$class = __CLASS__;

		\Nette\Forms\Container::extensionMethod('addDate',
			function (Container $form, $name, $label = NULL) use ($class) {
				$component = new $class($label, ParentDateInput::TYPE_DATE);
				$form->addComponent($component, $name);
				return $component;
			});

		\Nette\Forms\Container::extensionMethod('addTime',
			function (Container $form, $name, $label = NULL) use ($class) {
				$component = new $class($label, ParentDateInput::TYPE_TIME);
				$form->addComponent($component, $name);
				return $component;
			});

		\Nette\Forms\Container::extensionMethod('addDateTime',
			function (Container $form, $name, $label = NULL) use ($class) {
				$component = new $class($label, ParentDateInput::TYPE_DATETIME);
				$form->addComponent($component, $name);
				return $component;
			});

		Rules::$defaultMessages[':dateInputRange'] =
			Rules::$defaultMessages[Form::RANGE];

		Rules::$defaultMessages[':dateInputValid'] =
			'Please enter a valid date.';
	}

	public function __construct($label = null, $type = self::TYPE_DATETIME_LOCAL) {

		parent::__construct($label, $type);

		$this->control->autocomplete = 'off';

		$this->monitor('Nette\Application\UI\Presenter');

	}

	/**
	 * rekonstruuje DateTime z pole
	 */
	public function setValue($value = NULL) {

		if (is_array($value)) {
			// zónu ignorujeme
			$value = new DateTime($value['date']);
		}

		parent::setValue($value);

	}

	protected function attached($form) {

		parent::attached($form);

		if ($form instanceof Presenter) {
			$form->addJQueryUiScript();
			$form->addScript('jquery-ui-timepicker-addon.js');
			$form->addScript('jquery-ui-sliderAccess.js');
			$form->addScript('jquery.ui.datepicker-cs.js');
			$form->addScript('jquery.ui.timepicker-cs.js');
			$form->addScript('dateInput.js');
			$form->addScript('dateInput.init.js');
		}

	}

}
