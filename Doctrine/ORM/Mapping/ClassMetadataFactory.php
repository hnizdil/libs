<?php

namespace Hnizdil\Doctrine\ORM\Mapping;

class ClassMetadataFactory
	extends \Doctrine\ORM\Mapping\ClassMetadataFactory
{

	/**
	 * {@inheritdoc}
	 *
	 * @param string $className
	 * @return \Hnizdil\Doctrine\ORM\Mapping\ClassMetadata
	 */
	protected function newClassMetadataInstance($className) {

		return new ClassMetadata($className);

	}

}
