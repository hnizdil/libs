<?php

namespace Hnizdil\Nette\Security;

use Exception;
use SimpleXMLElement;
use Nette\Http\Url;
use Nette\Security\Identity;
use Nette\Security\IAuthenticator;
use Nette\Security\AuthenticationException;
use Doctrine\ORM\EntityManager;
use Hnizdil\Money;
// User already exists in ShipitoSpace\Nette\Security
use Entity\User as UserEntity;

class WordpressAuthenticator
	implements IAuthenticator
{

	protected $wpLoadPath;

	public function __construct($wpLoadPath) {

		$this->wpLoadPath = $wpLoadPath;

	}

	/**
	 * @param  array
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials) {

		list($username, $password) = $credentials;

		require_once $this->wpLoadPath;

		$wpUser = get_userdatabylogin($username);

		if (!wp_check_password($password, $wpUser->user_pass, $wpUser->ID)) {
			throw new AuthenticationException(
				'Chybné uživatelské jméno nebo heslo');
		}

		return new Identity($wpUser->ID, null, array(
			'name'  => $wpUser->display_name,
			'email' => $wpUser->user_email,
		));

	}

}
