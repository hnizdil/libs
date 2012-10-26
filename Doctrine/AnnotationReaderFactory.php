<?php

namespace Hnizdil\Doctrine;

use Doctrine\Common\Annotations\CachedReader,
	Doctrine\Common\Annotations\AnnotationReader,
	Doctrine\Common\Annotations\AnnotationRegistry;

class AnnotationReaderFactory
{

	public static function create($libsDir, $cache) {

		AnnotationRegistry::registerAutoloadNamespaces(array(
			'Hnizdil\\Annotation'    => $libsDir,
			'Doctrine\\ORM\\Mapping' => $libsDir,
		));

		return new CachedReader(new AnnotationReader(), $cache);

	}

}
