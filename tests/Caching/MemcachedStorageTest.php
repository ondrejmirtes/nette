<?php

namespace Nette\Caching;

require_once __DIR__ . '/CacheStorageTest.php';

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */
class MemcachedStorageTest extends CacheStorageTest
{
	public function setUp()
	{
		if (!MemcachedStorage::isAvailable()) {
			$this->markTestSkipped('MemcachedStorage is not available.');
		}

		parent::setUp();
	}

	protected function createStorage()
	{
		return new MemcachedStorage('localhost');
	}

	public function clearCache()
	{
		if (MemcachedStorage::isAvailable()) {
			$memcache = new \Memcache();
			$memcache->connect('localhost');
			$memcache->flush();
			$memcache->close();
		}

		parent::clearCache();
	}

	/**
	 * @dataProvider simpleDataProvider
	 * @group Timing
	 */
	public function testDependentItems($key, $value)
	{
		$this->markTestSkipped('Dependent items are not supported by MemcachedStorage.');
	}
}

