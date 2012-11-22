<?php

namespace Hnizdil\Annotation;

/**
 * @Annotation
 */
class Column {

	/**
	 * @var string
	 */
	public $title = '';

	/**
	 * @var boolean
	 */
	public $isSortable = TRUE;

	/**
	 * Sloupec je handle pro drag'n'drop jQueryUI řazení.
	 *
	 * @var boolean
	 */
	public $isSortingHandle = FALSE;

	/**
	 * @var boolean
	 */
	public $autoAdd = TRUE;

	/**
	 * @var string
	 */
	public $boolTrueValue = 'Ano';

	/**
	 * @var string
	 */
	public $boolFalseValue = 'Ne';

	/**
	 * @var string
	 */
	public $defaultSortType = '';

	/**
	 * @var string
	 */
	public $cellCssClassAppend = '';

	/**
	 * @var boolean
	 */
	public $useShortName = FALSE;

	/**
	 * @var array<string>
	 */
	public $format = array(
		'date'     => 'd.m.Y',
		'time'     => 'H:i:s',
		'datetime' => 'd.m.Y H:i:s',
	);

}
