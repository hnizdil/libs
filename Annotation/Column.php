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
	public $cellClass = '';

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
