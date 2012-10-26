<?php

namespace Hnizdil\Doctrine;

class Cache
	extends \Doctrine\Common\Cache\CacheProvider
{

	const CACHE_KEY = 'data';

	private $netteCache = NULL;

	private $data = array();

	public function __construct(\Nette\Caching\Cache $cache, $namespace) {

		$this->netteCache = $cache->derive($namespace);

		$this->data = $this->netteCache->load(self::CACHE_KEY);

		if ($this->data === NULL) {
			$this->data = array();
		}

	}

	public function __destruct() {

		$this->netteCache->save(self::CACHE_KEY, $this->data);

	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFetch($id) {

		return array_key_exists($id, $this->data) ? $this->data[$id] : FALSE;

	}

	/**
	 * {@inheritdoc}
	 */
	protected function doContains($id) {

		return array_key_exists($id, $this->data);

	}

	/**
	 * {@inheritdoc}
	 */
	protected function doSave($id, $data, $lifeTime = 0) {

		$this->data[$id] = $data;

		return TRUE;

	}

	/**
	 * {@inheritdoc}
	 */
	protected function doDelete($id) {

		unset($this->data[$id]);

		return TRUE;

	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFlush() {

		$this->data = array();

		return TRUE;

	}

	/**
	 * {@inheritdoc}
	 */
	protected function doGetStats() {

		return NULL;

	}

}
