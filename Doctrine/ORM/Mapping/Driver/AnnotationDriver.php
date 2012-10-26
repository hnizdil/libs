<?php

namespace Hnizdil\Doctrine\ORM\Mapping\Driver;

use Hnizdil\Annotation as a;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class AnnotationDriver
	extends \Doctrine\ORM\Mapping\Driver\AnnotationDriver
{

	public function loadMetadataForClass(
		$className,
		ClassMetadataInfo $metadata
	) {

		parent::loadMetadataForClass($className, $metadata);

		$reflClass = $metadata->getReflectionClass();

		foreach ($reflClass->getProperties() as $reflField) {

			$fieldAnnotations = $this->_reader->getPropertyAnnotations($reflField);
			foreach ($fieldAnnotations as $annotation) {
				if ($annotation instanceof a\Name) {
					$metadata->addNameField($reflField->name);
					continue;
				}
				if ($annotation instanceof a\ShortName) {
					$metadata->addShortNameField($reflField->name);
					continue;
				}
				if ($annotation instanceof a\Input) {
					$metadata->addFormField($reflField->name,
						get_object_vars($annotation));
					continue;
				}
				if ($annotation instanceof a\Column) {
					$metadata->addGridField($reflField->name,
						get_object_vars($annotation));
					continue;
				}
				if ($annotation instanceof a\DetailLink) {
					$metadata->addDetailLinkField($reflField->name);
					continue;
				}
			}

		}

		$reader = $this->_reader;

		$doClassAnnotations = function($reflClass) use ($reader, $metadata) {
			foreach ($reader->getClassAnnotations($reflClass) as $annotation) {
				if ($annotation instanceof a\GridActions) {
					$metadata->setGridActions($annotation->value);
				}
				if ($annotation instanceof a\GridMultiActions) {
					$metadata->setGridMultiActions($annotation->value);
				}
				if ($annotation instanceof a\GridOrder) {
					$metadata->setGridOrder($annotation->value);
				}
			}
		};

		do {
			$doClassAnnotations($reflClass);
		}
		while ($reflClass = $reflClass->getParentClass());

		$this->substituteAssociationMappings($metadata);

	}

	private function substituteAssociationMappings($metadata) {

		$Class = substr($metadata->name, strlen($metadata->namespace) + 1);
		$class = lcfirst($Class);

		// $replaces
		$r = array(
			'%Class%' => $Class,
			'%class%' => $class,
		);

		// associations
		foreach ($metadata->associationMappings as &$am) {
			$am['mappedBy']     = strtr($am['mappedBy'],     $r);
			$am['targetEntity'] = strtr($am['targetEntity'], $r);
			if (isset($am['joinTable']['name'])) {
				$am['joinTable']['name'] = strtr($am['joinTable']['name'], $r);
			}
		}

		// form inputs
		foreach ($metadata->formFields as &$ff) {
			$ff['uploadDirParam']    = strtr($ff['uploadDirParam'],    $r);
			$ff['uploadDirNamePath'] = strtr($ff['uploadDirNamePath'], $r);
		}

	}

}
