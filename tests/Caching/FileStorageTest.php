<?php

namespace Nette\Caching;

use Nette;


/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */
class FileStorageTest extends \TestCase
{
	public static function setUpBeforeClass()
	{
		// FIXME: FileStorage should not be dependent on tempDir through 
		// ICacheJournal
		Nette\Environment::setVariable('tempDir', __DIR__ . '/tmp');

		if (!@mkdir(Nette\Environment::getVariable('tempDir'), 0755) &&
			!is_dir(Nette\Environment::getVariable('tempDir')))
		{
			throw new \Exception('Cannot create ' . Nette\Environment::getVariable('tempDir') . '.');
		}
	}

	public static function tearDownAfterClass()
	{
		rmrf(Nette\Environment::getVariable('tempDir'));
	}

	protected $cache;
	protected $key;
	protected $value;

	public function setUp()
	{
		$this->cache = new Cache(new FileStorage(Nette\Environment::getVariable('tempDir')));
		$this->key = '../' . implode('', range("\x00", "\x1F"));
		$this->value = range("\x00", "\x1F");
	}

	public function testNotCached()
	{
		$key = uniqid('', TRUE);
		$this->assertFalse(isset($this->cache[$key]));
		$this->assertNull($this->cache[$key]);
	}

	public function basicsProvider()
	{
		$value = range("\x00", "\x1F");

		return array(
			array('../' . implode('', range("\x00", "\x1F")), $value),
			array(0xF00, $value),
		);
	}

	/**
	 * @dataProvider basicsProvider
	 */
	public function testWriteToCache($key, $value)
	{
		$this->cache[$key] = $value;
		$this->cache->release();

		$this->assertTrue(isset($this->cache[$key]));
		$this->assertEquals($this->cache[$key], $value);
	}

	/**
	 * @dataProvider basicsProvider
	 */
	public function testRemoveFromCacheUsingUnset($key, $value)
	{
		$this->cache[$key] = $value;
		$this->cache->release();
		$this->assertTrue(isset($this->cache[$key]));

		unset($this->cache[$key]);
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$key]));
		$this->assertNull($this->cache[$key]);
	}

	/**
	 * @dataProvider basicsProvider
	 */
	public function testRemoveFromCacheUsingNull($key, $value)
	{
		$this->cache[$key] = $value;
		$this->cache->release();
		$this->assertTrue(isset($this->cache[$key]));

		$this->cache[$key] = NULL;
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$key]));
		$this->assertNull($this->cache[$key]);
	}

	/**
	 * @dataProvider basicsProvider
	 */
	public function testRemoveFromCacheUsingNullCallback($key, $value)
	{
		$this->cache[$key] = $value;
		$this->cache->release();
		$this->assertTrue(isset($this->cache[$key]));

		$this->cache->save($key, function () {
			return NULL;
		});
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$key]));
		$this->assertNull($this->cache[$key]);
	}

	public function testCallbackDependencyOk()
	{
		$this->cache->save($this->key, $this->value, array(
			Cache::CALLBACKS => array(array(array(__CLASS__, 'dependency'), 1)),
		));
		$this->cache->release();

		$this->assertTrue(isset($this->cache[$this->key]));
	}

	public function testCallbackDependencyNotOk()
	{
		$this->cache->save($this->key, $this->value, array(
			Cache::CALLBACKS => array(array(array(__CLASS__, 'dependency'), 0)),
		));
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$this->key]));
	}

	public static function dependency($ret)
	{
		return $ret;
	}

	public function testStorageUsedByMoreCaches()
	{
		$anotherCache = new Cache($this->cache->getStorage(), 'anotherCache');

		$this->cache['foo'] = 'foo';
		$this->cache['bar'] = 'bar';
		$anotherCache['foo'] = 'another foo';
		$anotherCache['bar'] = 'another bar';

		$this->assertEquals($this->cache['foo'], 'foo');
		$this->assertEquals($this->cache['bar'], 'bar');
		$this->assertEquals($anotherCache['foo'], 'another foo');
		$this->assertEquals($anotherCache['bar'], 'another bar');

		unset($this->cache['bar']);
		unset($anotherCache['foo']);
		$this->cache->release();
		$anotherCache->release();

		$this->assertTrue(isset($this->cache['foo']));
		$this->assertFalse(isset($anotherCache['foo']));
		$this->assertFalse(isset($this->cache['bar']));
		$this->assertTrue(isset($anotherCache['bar']));
	}

	public function testCleanAll()
	{
		$anotherCache = new Cache($this->cache->getStorage(), 'anotherCache');

		$this->cache['foo'] = 'foo';
		$this->cache['bar'] = 'bar';
		$anotherCache['foo'] = 'another foo';
		$anotherCache['bar'] = 'another bar';

		$this->cache->getStorage()->clean(array(Cache::ALL => TRUE));

		$this->assertNull($this->cache['foo']);
		$this->assertNull($this->cache['bar']);
		$this->assertNull($anotherCache['foo']);
		$this->assertNull($anotherCache['bar']);
	}

	public function testWriteUsingClosure()
	{
		$value = $this->value;

		$this->assertEquals($this->cache->save($this->key, function () use ($value) {
			return $value;
		}), $value);

		$this->cache->release();

		$this->assertEquals($this->cache[$this->key], $value);
	}

	public function testWriteUsingCallback()
	{
		$value = $this->value;

		$this->assertEquals($this->cache->save($this->key, callback(function () use ($value) {
			return $value;
		})), $value);

		$this->cache->release();

		$this->assertEquals($this->cache[$this->key], $value);
	}

	public function testExpiration()
	{
		$this->cache->save($this->key, $this->value, array(
			Cache::EXPIRE => time() + 3,
		));

		sleep(1);
		clearstatcache();
		$this->cache->release();

		$this->assertTrue(isset($this->cache[$this->key]));

		sleep(3);
		clearstatcache();
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

	public function testDependent()
	{
		$this->cache->save($this->key, $this->value, array(
			Cache::ITEMS => array('dependent'),
		));
		$this->cache->release();
		$this->assertTrue(isset($this->cache[$this->key]));

		$this->cache['dependent'] = 'foo';
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$this->key]));

		$this->cache->save($this->key, $this->value, array(
			Cache::ITEMS => array('dependent'),
		));
		$this->cache->release();
		$this->assertTrue(isset($this->cache[$this->key]));

		sleep(2);
		$this->cache['dependent'] = 'bar';
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$this->key]));

		$this->cache->save($this->key, $this->value, array(
			Cache::ITEMS => array('dependent'),
		));
		$this->cache->release();
		$this->assertTrue(isset($this->cache[$this->key]));

		$this->cache['dependent'] = NULL;
		$this->cache->release();

		$this->assertFalse(isset($this->cache[$this->key]));
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
