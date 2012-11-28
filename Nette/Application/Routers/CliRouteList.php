<?php

namespace Hnizdil\Nette\Application\Routers;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\CliRouter;

/**
 * V případě spuštění přes CLI přidá jako první routu CliRouter.
 */
class CliRouteList
	extends RouteList
{

	public function __construct($module = NULL) {

		parent::__construct($module);

		if (php_sapi_name() === 'cli') {
			$this[] = new CliRouter;
		}

	}

}
