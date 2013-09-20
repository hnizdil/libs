<?php

namespace Hnizdil\Nette\Http;

use mysqli;
use Nette\Http\ISessionStorage;

/*

CREATE TABLE `sessions` (
	`id` varbinary(32) NOT NULL,
	`data` blob NOT NULL,
	`timestamp` timestamp NOT NULL
		DEFAULT CURRENT_TIMESTAMP
		ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM;

 */

/**
 * Modified
 * http://www.php.net/manual/en/function.session-set-save-handler.php#79706
 */
class MySqlSessionStorage
	implements ISessionStorage
{

	private $host;
	private $user;
	private $pass;
	private $db;
	private $table;

	/**
	 * a database connection resource
	 * @var resource
	 */
	private $mysqli;

	public function __construct($host, $user, $pass, $db, $table) {

		$this->host  = $host;
		$this->user  = $user;
		$this->pass  = $pass;
		$this->db    = $db;
		$this->table = $table;

	}

	/**
	 * Open the session
	 * @return bool
	 */
	public function open($savePath, $sessionName) {

		// TODO: $savePath, $sessionName
		//error_log($savePath);
		//error_log($sessionName);

		$this->mysqli = new mysqli(
			$this->host,
			$this->user,
			$this->pass,
			$this->db
		);

		if ($this->mysqli->connect_errno) {
			throw new Exception($this->mysqli->connect_error);
		}

		register_shutdown_function('session_write_close');

		return true;

	}

	/**
	 * Close the session
	 * @return bool
	 */
	public function close() {

		return $this->mysqli->close();

	}

	/**
	 * Read the session
	 * @param int session id
	 * @return string string of the sessoin
	 */
	public function read($id) {

		$stmt = $this->mysqli->prepare(
			"SELECT `data` FROM `{$this->table}` WHERE id = ?");

		$stmt->bind_param('s', $id);

		$stmt->execute();
		$stmt->store_result();

		if ($stmt->num_rows) {
			$stmt->bind_result($data);
			$stmt->fetch();
			return $data;
		}

		return '';

	}

	/**
	 * Write the session
	 * @param int session id
	 * @param string data of the session
	 */
	public function write($id, $data) {

		$stmt = $this->mysqli->prepare(
			"REPLACE INTO `{$this->table}` (`id`, `data`) VALUES (?, ?)");

		$stmt->bind_param('ss', $id, $data);

		return $stmt->execute();

	}

	/**
	 * Destoroy the session
	 * @param int session id
	 * @return bool
	 */
	public function remove($id) {

		$stmt = $this->mysqli->prepare(
			"DELETE FROM `{$this->table}` WHERE `id` = ?");

		$stmt->bind_param('s', $id);

		return $stmt->execute();

	}

	/**
	 * Garbage Collector
	 * @param int life time (sec.)
	 * @return bool
	 * @see session.gc_divisor      100
	 * @see session.gc_maxlifetime 1440
	 * @see session.gc_probability    1
	 * @usage execution rate 1/100
	 *        (session.gc_probability/session.gc_divisor)
	 */
	public function clean($maxlifetime) {

		$stmt = $this->mysqli->prepare(
			"DELETE FROM `{$this->table}` WHERE `timestamp` < ?");

		$stmt->bind_param('i', time() - $maxlifetime);

		return $stmt->execute();

	}

	public function setHost($host) {

		$this->host = $host;

	}

	public function setUser($user) {

		$this->user = $user;

	}

	public function setPass($pass) {

		$this->pass = $pass;

	}

	public function setDb($db) {

		$this->db = $db;

	}

	public function setTable($table) {

		$this->table = $table;

	}

}
