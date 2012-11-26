<?php

namespace Hnizdil\Doctrine\ORM\Query\AST\Functions;

/**
 * "numeric YEAR(date)"
 */
class YearFunction
	extends SingleDateParamNumericResultFunction
{

	protected $mySqlName = 'YEAR';

}
