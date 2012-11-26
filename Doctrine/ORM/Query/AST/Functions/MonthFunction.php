<?php

namespace Hnizdil\Doctrine\ORM\Query\AST\Functions;

/**
 * "numeric MONTH(date)"
 */
class MonthFunction
	extends SingleDateParamNumericResultFunction
{

	protected $mySqlName = 'MONTH';

}
