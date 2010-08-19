<?php

namespace Nette\Caching;

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */
class MemcachedStorageTest extends \TestCase
{
	protected $cache;

	protected $key = 'nette';
	protected $value = 'rulez';

	public function setUp()
	{
		if (!MemcachedStorage::isAvailable()) {
			$this->markTestSkipped('MemcachedStorage is not available.');
		}

		$this->cache = new Cache(new MemcachedStorage('localhost'));
	}

	public function testExpiration()
	{
		$this->cache->save($this->key, $this->values, array(
			Cache::EXPIRE => time() + 2,
		));
		$this->cache->release();
		$this->assertTrue(isset($this->cache[$this->key]));

		usleep(1200000);
		$this->cache->release();
		$this->assertTrue(isset($this->cache[$this->key]));

		usleep(1200000);
		$this->cache->release();
		$this->assertFalse(isset($this->cache[$this->key]));
	}

	public function testFileDependency()
	{
		$file = Nette\Environment::getVariable('tempDir') . '/foo';
		@unlink($file);

		$this->cache->save($this->key, $this->value, array(
			Cache::FILES => array(__FILE__, $file)
		));
		$this->cache->release();

		$this->assertTrue(isset($this->cache[$this->key]));

		file_put_contents($file, 'bar');
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$this->key]));

		$this->cache->save($this->key, $this->value, array(
			Cache::FILES => array(__FILE__, $file)
		));
		$this->cache->release();

		$this->assertTrue(isset($this->cache[$this->key]));

		sleep(2);
		file_put_contents($file, 'baz');
		clearstatcache();
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$this->key]));

		unlink($file);
	}

	public function testPriority()
	{
		for ($i = 1; $i <= 3; ++$i) {
			$this->cache->save($i, $i, array(
				Cache::PRIORITY => $i * 100,
			));
		}

		$this->cache->save(4, 4);

		$this->cache->clean(array(
			Cache::PRIORITY => '200',
		));

		$this->assertFalse(isset($this->cache[1]));
		$this->assertFalse(isset($this->cache[2]));
		$this->assertTrue(isset($this->cache[3]));
		$this->assertTrue(isset($this->cache[4]));
	}

	public function testSliding()
	{
		$this->cache->save($this->key, $this->value, array(
			Cache::EXPIRE => time() + 2,
			Cache::SLIDING => TRUE,
		));
		$this->cache->release();
		$this->assertTrue(isset($this->cache[$this->key]));

		for ($i = 0; $i < 3; ++$i) {
			sleep(1);
			clearstatcache();
			$this->cache->release();
			$this->assertTrue(isset($this->cache[$this->key]));
		}

		sleep(3);
		clearstatcache();
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$this->key]));
	}

	public function testTags()
	{
		$this->cache->save(1, 1, array(
			Cache::TAGS => array('a', 'b'),
		));

		$this->cache->save(2, 2, array(
			Cache::TAGS => array('a', 'c'),
		));

		$this->cache->save(3, 3, array(
			Cache::TAGS => array('b', 'c'),
		));

		$this->cache[4] = 4;

		$this->cache->clean(array(
			Cache::TAGS => 'a',
		));

		$this->assertFalse(isset($this->cache[1]));
		$this->assertFalse(isset($this->cache[2]));
		$this->assertTrue(isset($this->cache[3]));
		$this->assertTrue(isset($this->cache[4]));
	}
}

// vim: noexpandtab softtabstop=4 tabstop=4 shiftwidth=4 nolist
