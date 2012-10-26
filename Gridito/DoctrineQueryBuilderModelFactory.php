<?php

namespace Hnizdil\Gridito;

use Doctrine\ORM\QueryBuilder;

class DoctrineQueryBuilderModelFactory
{

	public function create(QueryBuilder $queryBuilder) {

		return new DoctrineQueryBuilderModel($queryBuilder);

	}

}
