<?php
/**
 * This file is part of the Code-Insight library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/console-helpers/code-insight
 */

namespace ConsoleHelpers\CodeInsight\Cache;


use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;

class CacheFactory
{

	/**
	 * Namespace.
	 *
	 * @var string
	 */
	private $_namespace;

	/**
	 * CacheFactory constructor.
	 *
	 * @param string $namespace Namespace.
	 */
	public function __construct($namespace)
	{
		$this->_namespace = $namespace;
	}

	/**
	 * Creates cache by name.
	 *
	 * @param string $name    Name.
	 * @param array  $options Options.
	 *
	 * @return CacheProvider
	 * @throws \InvalidArgumentException When cache provider with given name not found.
	 * @throws \LogicException When no caches provided for "chain" cache.
	 */
	public function create($name, array $options = array())
	{
		switch ( $name ) {
			case 'chain':
				$valid_caches = array();

				foreach ( array_filter($options) as $cache_name ) {
					$valid_caches[] = self::create($cache_name);
				}

				if ( !$valid_caches ) {
					throw new \LogicException('No valid caches provided for "chain" cache.');
				}

				$cache_driver = new ChainCache($valid_caches);
				$cache_driver->setNamespace($this->_namespace);

				return $cache_driver;

			case 'array':
				$cache_driver = new ArrayCache();
				$cache_driver->setNamespace($this->_namespace);

				return $cache_driver;

			case 'apc':
				$cache_driver = new ApcCache();
				$cache_driver->setNamespace($this->_namespace);

				return $cache_driver;

			case 'memcache':
				$memcache = new \Memcache();
				$memcache->connect('localhost', 11211);

				$cache_driver = new MemcacheCache();
				$cache_driver->setMemcache($memcache);
				$cache_driver->setNamespace($this->_namespace);

				return $cache_driver;

			case 'memcached':
				$memcached = new \Memcached();
				$memcached->addServer('memcache_host', 11211);

				$cache_driver = new MemcachedCache();
				$cache_driver->setMemcached($memcached);
				$cache_driver->setNamespace($this->_namespace);

				return $cache_driver;
		}

		throw new \InvalidArgumentException('Cache provider "' . $name . '" not found.');
	}

}
