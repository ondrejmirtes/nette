<?php

namespace Nette\Caching;

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */
class DummyStorageTest extends \TestCase
{
	protected $cache;

	protected $key = 'nette';
	protected $value = '"Hello World"';

	public function setUp()
	{
		$this->cache = new Cache(new DummyStorage, 'myspace');
	}

	public function testNotCachedYet()
	{
		$this->assertFalse(isset($this->cache[$this->key]));
		$this->assertNull($this->cache[$this->key]);
	}

	/**
	 * @dependens testNotCachedYet
	 */
	public function testWriteToCache()
	{
		$this->cache[$this->key] = $this->value;
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$this->key]));
		$this->assertNotEquals($this->cache[$this->key], $this->value);
	}

	public function testUnsetFromCache()
	{
		$this->cache[$this->key] = $this->value;
		unset($this->cache[$this->key]);
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$this->key]));
	}

	public function testRemoveFromCacheUsingNull()
	{
		$this->cache[$this->key] = $this->value;
		$this->cache[$this->key] = NULL;
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$this->key]));
	}
}

// vim: noexpandtab softtabstop=4 tabstop=4 shiftwidth=4 nolist
