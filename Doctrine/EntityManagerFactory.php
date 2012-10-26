<?php

namespace Hnizdil\Doctrine;

use Doctrine\Common\Cache\Cache,
	Doctrine\ORM\Mapping\Driver\Driver,
	Doctrine\ORM\Configuration,
	Doctrine\ORM\EntityManager,
	Doctrine\Common\EventManager,
	Nella\Addons\Doctrine\Diagnostics\ConnectionPanel;

class EntityManagerFactory
{

	public static function create(
		Cache $metaCache,
		Cache $queryCache,
		EventManager $eventManager,
		Driver $annotationDriver,
		$proxyDir,
		$proxyNamespace,
		$databaseConfig,
		$autogenerateProxyClasses,
		$classMetadataFactoryName,
		$productionMode
	) {

		$config = new Configuration();
		$config->setMetadataDriverImpl($annotationDriver);
		$config->setProxyDir($proxyDir);
		$config->setProxyNamespace($proxyNamespace);
		$config->setAutoGenerateProxyClasses($autogenerateProxyClasses);
		$config->setQueryCacheImpl($queryCache);
		$config->setMetadataCacheImpl($metaCache);
		$config->setClassMetadataFactoryName($classMetadataFactoryName);

		if (!$productionMode) {
			$config->setSQLLogger(ConnectionPanel::register());
		}

		return EntityManager::create($databaseConfig, $config, $eventManager);

	}

}
