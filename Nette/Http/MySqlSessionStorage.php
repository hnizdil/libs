<?php

namespace Hnizdil\Nette\Http;

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
	private $dbh;

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

		$this->dbh = mysql_connect(
			$this->host,
			$this->user,
			$this->pass
		);

		if ($this->dbh) {
			register_shutdown_function('session_write_close');
			return mysql_select_db($this->db, $this->dbh);
		}

		return false;

	}

	/**
	 * Close the session
	 * @return bool
	 */
	public function close() {

		return mysql_close($this->dbh);

	}

	/**
	 * Read the session
	 * @param int session id
	 * @return string string of the sessoin
	 */
	public function read($id) {

		$sql = sprintf("SELECT `data` FROM `%s` WHERE id = '%s'",
			mysql_real_escape_string($this->table),
			mysql_real_escape_string($id));

		if ($result = mysql_query($sql, $this->dbh)) {
			if (mysql_num_rows($result)) {
				$record = mysql_fetch_assoc($result);
				return $record['data'];
			}
		}

		return '';

	}

	/**
	 * Write the session
	 * @param int session id
	 * @param string data of the session
	 */
	public function write($id, $data) {

		$sql = sprintf("REPLACE INTO `%s` (`id`, `data`) VALUES('%s', '%s')",
			mysql_real_escape_string($this->table),
			mysql_real_escape_string($id),
			mysql_real_escape_string($data));

		return mysql_query($sql, $this->dbh);

	}

	/**
	 * Destoroy the session
	 * @param int session id
	 * @return bool
	 */
	public function remove($id) {

		$sql = sprintf("DELETE FROM `%s` WHERE `id` = '%s'",
			mysql_real_escape_string($this->table),
			mysql_real_escape_string($id));

		return mysql_query($sql, $this->dbh);

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

		$sql = sprintf("DELETE FROM `%s` WHERE `timestamp` < %d",
			mysql_real_escape_string($this->table),
			time() - $maxlifetime);

		return mysql_query($sql, $this->dbh);

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
