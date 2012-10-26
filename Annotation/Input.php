<?php

namespace Hnizdil\Annotation;

/**
 * @Annotation
 */
class Input {

	/**
	 * @var string
	 */
	public $title = '';

	/**
	 * @var string
	 */
	public $description = '';

	/**
	 * @var string
	 */
	public $control = '';

	/**
	 * @var string
	 */
	public $controlPrompt = '– vyberte –';

	/**
	 * @var string
	 */
	public $optionalControlPrompt = '';

	/**
	 * @var string
	 */
	public $setter = '';

	/**
	 * @var string
	 */
	public $unsetter = '';

	/**
	 * @var string
	 */
	public $uploadDirParam = '';

	/**
	 * @var string
	 */
	public $uploadDirNamePath = '';

	/**
	 * @var string
	 */
	public $currency = 'cs-CZ';

	/**
	 * @var boolean
	 */
	public $required = NULL;

	/**
	 * @var boolean
	 */
	public $itemsSorted = TRUE;

	/**
	 * @var boolean
	 */
	public $wysiwyg = FALSE;

	/**
	 * @var array<string>
	 */
	public $targetNameCols = array();

	/**
	 * @var array<string>
	 */
	public $allowedValues = array();

	/**
	 * @var array<string>
	 */
	public $targetNameColsSeparator = ' – ';

	/**
	 * @var boolean
	 */
	public $editableEntity = FALSE;

	/**
	 * @var int
	 */
	public $editableEntityInitCount = 0;

	/**
	 * @var int
	 */
	public $editableEntityMaxCount = 0;

	/**
	 * @var int
	 */
	public $editableEntityMinCount = 0;

	/**
	 * @var int
	 */
	public $editableEntityAdditionalCount = 0;

}
