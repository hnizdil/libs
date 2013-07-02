<?php

namespace Hnizdil\Doctrine;

use Nette\Caching\Cache as NetteCache;
use Doctrine\Common\Cache\CacheProvider;

class Cache
	extends CacheProvider
{

	private $cache;

	private $data = array();

	public function __construct(NetteCache $cache, $namespace) {

		$this->cache = $cache->derive($namespace);

	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFetch($id) {

		$entry = $this->cache->load($id);

		if ($entry === null) {
			$entry = false;
		}

		return $entry;

	}

	/**
	 * {@inheritdoc}
	 */
	protected function doContains($id) {

		return $this->doFetch($id) !== false;

	}

	/**
	 * {@inheritdoc}
	 */
	protected function doSave($id, $data, $lifeTime = false) {

		$options = array();

		if ($lifeTime !== 0) {
			$options[NetteCache::EXPIRE] = $lifeTime;
		}

		$this->cache->save($id, $data, $options);

		return TRUE;

	}

	/**
	 * {@inheritdoc}
	 */
	protected function doDelete($id) {

		$this->cache->remove($id);

		return TRUE;

	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFlush() {

		$this->cache->clean(array(
			NetteCache::ALL => true,
		));

		return TRUE;

	}

	/**
	 * {@inheritdoc}
	 */
	protected function doGetStats() {

		return NULL;

	}

}
