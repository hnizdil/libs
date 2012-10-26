<?php

namespace Hnizdil\Gridito;

use Doctrine\ORM\EntityManager;

class DoctrineModelFactory
	extends DoctrineQueryBuilderModel
{

	private $em;
	private $doctrineQueryBuilderModelFactory;

	public function __construct(
		EntityManager                    $em,
		DoctrineQueryBuilderModelFactory $doctrineQueryBuilderModelFactory
	) {

		$this->em = $em;
		$this->doctrineQueryBuilderModelFactory =
			$doctrineQueryBuilderModelFactory;

	}

	public function create($entityClassName) {

		$queryBuilder = $this->em
			->getRepository($entityClassName)
			->createQueryBuilder('e')->distinct();

		$model = $this->doctrineQueryBuilderModelFactory->create($queryBuilder);

		$model->setEntityClassName($entityClassName);

		return $model;

	}

}
