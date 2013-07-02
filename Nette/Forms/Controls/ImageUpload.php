<?php

namespace Hnizdil\Nette\Forms\Controls;

use Nette\Utils\Html;
use Nette\Forms\Container;

class ImageUpload
	extends AbstractUpload
{

	public function getThumbnail() {

		return Html::el('img')
			->src($this->getOption('wwwPath'));

	}

	public static function register() {

		Container::extensionMethod('addImageUpload', function (Container $container, $name, $label = null) {
			$class = __CLASS__;
			return $container[$name] = new $class($label);
		});

	}

}
