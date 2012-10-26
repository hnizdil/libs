<?php

namespace Hnizdil\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class GridOrder {

	/**
	 * @var array<string>
	 */
	public $value = array();

}
