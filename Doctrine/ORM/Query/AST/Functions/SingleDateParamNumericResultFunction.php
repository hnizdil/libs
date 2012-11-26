<?php

namespace Hnizdil\Doctrine\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * "numeric <name>(date)"
 */
class SingleDateParamNumericResultFunction
	extends FunctionNode
{

	public $dateExpression = NULL;

	public function getSql(SqlWalker $sqlWalker) {

		return sprintf('%s(%s)',
			$this->mySqlName,
			$this->dateExpression->dispatch($sqlWalker));

	}

	public function parse(Parser $parser) {

		$parser->match(Lexer::T_IDENTIFIER);
		$parser->match(Lexer::T_OPEN_PARENTHESIS);

		$this->dateExpression = $parser->ArithmeticPrimary();

		$parser->match(Lexer::T_CLOSE_PARENTHESIS);

	}
}
