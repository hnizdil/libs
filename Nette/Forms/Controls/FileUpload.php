<?php

namespace Hnizdil\Nette\Forms\Controls;

use Nette\Utils\Html;
use Nette\Forms\Container;

class FileUpload
	extends AbstractUpload
{

	public function getThumbnail() {

		return Html::el();

	}

	public static function register() {

		Container::extensionMethod('addFileUpload', function (Container $container, $name, $label = null) {
			$class = __CLASS__;
			return $container[$name] = new $class($label);
		});

	}

}
