<?php

namespace Hnizdil\Doctrine\ORM\Query\AST\Functions;

/**
 * "numeric DAY(date)"
 */
class DayFunction
	extends SingleDateParamNumericResultFunction
{

	protected $mySqlName = 'DAY';

}
