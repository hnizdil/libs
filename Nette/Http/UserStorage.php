<?php

namespace Hnizdil\Nette\Http;

use Doctrine\ORM\EntityManager,
	Nette\ObjectMixin,
	Nette\Security\User,
	Nette\Security\IIdentity;

class UserStorage
	extends \Nette\Http\UserStorage
{

	private $em;
	private $entityFQCN;
	private $idField;

	public function setIdentity(IIdentity $identity = NULL) {

		$this->identity = $identity;

		if ($identity instanceof User) {
			$user = new User;
			ObjectMixin::set($user, $this->idField, $identity->getId());
			$identity = $user;
		}

		return parent::setIdentity($identity);

	}

	public function getIdentity() {

		$identity = parent::getIdentity();

		if ($identity === NULL) {
			return $identity;
		}

		return $this->em
			->getRepository($this->entityFQCN)
			->findOneBy(array($this->idField => $identity->getId()));

	}

	public function setEm(EntityManager $em) {

		$this->em = $em;

	}

	public function setEntityFQCN($entityFQCN) {

		$this->entityFQCN = $entityFQCN;

	}

	public function setIdField($idField) {

		$this->idField = $idField;

	}

}
