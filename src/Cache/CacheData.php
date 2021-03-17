<?php

	namespace App\Cache;

	use Symfony\Component\Cache\Adapter\AdapterInterface;
	use Symfony\Contracts\Cache\ItemInterface;

	class CacheData {

		private $cache;

		public function __construct(AdapterInterface $cache) {
			$this->cache = $cache;
		}

		public function getDataCached($cacheKey, $query) {

			return $this->cache->get($cacheKey, function (ItemInterface $item) use ($query) {
				$item->expiresAfter(3600);
				$item->set($query);
				$this->cache->save($item);
				return $item->get();
			});
		}

		public function deleteCache($key) {
			$this->cache->delete($key);
		}
	}