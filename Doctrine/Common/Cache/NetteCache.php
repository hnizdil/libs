<?php

namespace Hnizdil\Doctrine\Common\Cache;

use Nette\Caching\Cache as NCache;
use Doctrine\Common\Cache\CacheProvider;

class NetteCache
	extends CacheProvider
{

	private $cache;

	private $data = array();

	public function __construct(NCache $cache, $namespace) {

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
			$options[NCache::EXPIRE] = $lifeTime;
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
			NCache::ALL => true,
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
