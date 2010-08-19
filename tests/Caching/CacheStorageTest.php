<?php

namespace Nette\Caching;

if (function_exists('__phpunit_run_isolated_test')) {
	require_once __DIR__ . '/../bootstrap.php';
}


/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */
abstract class CacheStorageTest extends \TestCase
{
	protected $preserveGlobalState = FALSE;

	/********************* Test (de)initialisation *********************/

	/** @var Cache */
	protected $cache;

	/** @return ICacheStorage */
	protected abstract function createStorage();

	public function setUp()
	{
		$this->cache = new Cache($this->createStorage());
	}

	public function tearDown()
	{
		$this->clearCache();
	}


	public function clearCache()
	{
		$annotations = $this->getAnnotations();
		if (!isset($annotations['method']['keepCache'])) {
			$this->truncateDirectory(__DIR__ . '/../temp');
		}
	}



	/********************* Assertion methods *********************/

	public function assertCached($cache, $key = NULL, $value = NULL, $message = NULL)
	{
		if (!($cache instanceof Cache)) {
			$message = $value;
			$value = $key;
			$key = $cache;
			$cache = $this->cache;
		}
		if ($key === NULL) {
			throw new \InvalidArgumentException('The key has to be specified.');
		}
		if (!is_scalar($key)) {
			var_dump($key);
			throw new \InvalidArgumentException('The key has to be a scalar value.');
		}

		$cache->release();
		clearstatcache();
		$this->assertTrue(isset($cache[$key]), $message);
		if ($value !== NULL) {
			$this->assertEquals($value, $cache[$key], $message);
		}
	}

	public function assertNotCached($cache, $key = NULL, $message = NULL)
	{
		if (!($cache instanceof Cache)) {
			$message = $key;
			$key = $cache;
			$cache = $this->cache;
		}
		if ($key === NULL) {
			throw new \InvalidArgumentException('The key has to be specified.');
		}
		if (!is_scalar($key)) {
			throw new \InvalidArgumentException('The key has to be a scalar value.');
		}

		$cache->release();
		clearstatcache();
		$this->assertFalse(isset($cache[$key]));
		$this->assertNull($cache[$key]);
	}



	/********************* Data providers *********************/

	public function simpleDataProvider()
	{
		return array(
			array('nette', 'rulez')
		);
	}

	public function comprehensiveDataProvider()
	{
		return array(
			array('nette', 'rulez'),
			array('../' . implode('', range("\x00", "\x1F")), $value = range("\x00", "\xFF")),
			array(0xF00, $value)
		);
	}



	/********************* Tests: basic operations *********************/

	public function testNotCached()
	{
		$this->assertNotCached(uniqid('', TRUE));
	}


	/**
	 * @dataProvider comprehensiveDataProvider
	 */
	public function testWrite($key, $value)
	{
		$this->assertNotCached($key);
		$this->cache[$key] = $value;
		$this->assertCached($key, $value);
	}

	/**
	 * @dataProvider simpleDataProvider
	 * @testdox Write using closure
	 */
	public function testWriteClosure($key, $value)
	{
		$result = $this->cache->save($key, function () use($value) {
			return $value;
		});
		$this->assertEquals($value, $result);
		$this->assertCached($key, $value);
	}


	/**
	 * @dataProvider simpleDataProvider
	 * @testdox Write using callback
	 */
	public function testWriteCallback($key, $value)
	{
		$result = $this->cache->save($key, callback(function () use($value) {
			return $value;
		}));
		$this->assertEquals($value, $result);
		$this->assertCached($key, $value);
	}


	/**
	 * @dataProvider comprehensiveDataProvider
	 * @testdox Remove from cache using unset()
	 */
	public function testRemoveUsingUnset($key, $value)
	{
		$this->testWrite($key, $value);
		unset($this->cache[$key]);
		$this->assertNotCached($key);
	}


	/**
	 * @dataProvider comprehensiveDataProvider
	 * @testdox Remove from cache by setting NULL
	 */
	public function testRemoveUsingNull($key, $value)
	{
		$this->testWrite($key, $value);
		$this->cache[$key] = NULL;
		$this->assertNotCached($key);
	}


	/**
	 * @dataProvider comprehensiveDataProvider
	 * @testdox Remove from cache by setting callback returning NULL
	 */
	public function testRemoveUsingCallback($key, $value)
	{
		$this->testWrite($key, $value);
		$this->cache->save($key, function () {
			return NULL;
		});
		$this->assertNotCached($key);
	}


	/**
	 * @testdox Independent namespaces
	 */
	public function testNamespaces()
	{
		$anotherCache = new Cache($this->cache->getStorage(), 'anotherCache');
		$this->cache['foo'] = 'foo';
		$this->cache['bar'] = 'bar';
		$anotherCache['foo'] = 'another foo';
		$anotherCache['bar'] = 'another bar';

		$this->assertCached('foo', 'foo');
		$this->assertCached('bar', 'bar');
		$this->assertCached($anotherCache, 'foo', 'another foo');
		$this->assertCached($anotherCache, 'bar', 'another bar');

		unset($this->cache['bar']);
		$anotherCache['foo'] = NULL;

		$this->assertCached('foo', 'foo');
		$this->assertNotCached('bar');
		$this->assertNotCached($anotherCache, 'foo');
		$this->assertCached($anotherCache, 'bar', 'another bar');
	}


	/**
	 * @testdox Clean all sweeps clean
	 */
	public function testCleanAll()
	{
		$anotherCache = new Cache($this->cache->getStorage(), 'anotherCache');
		$this->cache['foo'] = 'foo';
		$this->cache['bar'] = 'bar';
		$anotherCache['foo'] = 'another foo';
		$anotherCache['bar'] = 'another bar';

		$this->cache->clean(array(Cache::ALL => TRUE));

		$this->assertNotCached('foo');
		$this->assertNotCached('bar');
		$this->assertNotCached($anotherCache, 'foo');
		$this->assertNotCached($anotherCache, 'bar');
	}



	/********************* Tests: dependencies *********************/

	public static function dependency($ret)
	{
		return $ret;
	}

	/**
	 * @dataProvider simpleDataProvider
	 * @testdox Callback dependency returning TRUE
	 */
	public function testCallbackDependencyTrue($key, $value)
	{
		$this->cache->save($key, $value, array(
			Cache::CALLBACKS => array(array(array(__CLASS__, 'dependency'), TRUE))
		));
		$this->assertCached($key);
	}

	/**
	 * @dataProvider simpleDataProvider
	 * @testdox Callback dependency returning FALSE
	 */
	public function testCallbackDependencyFalse($key, $value)
	{
		$this->cache->save($key, $value, array(
			Cache::CALLBACKS => array(array(array(__CLASS__, 'dependency'), FALSE))
		));
		$this->assertNotCached($key);
	}


	/**
	 * @dataProvider simpleDataProvider
	 * @group Timing
	 */
	public function testExpiration($key, $value)
	{
		$this->cache->save($key, $value, array(
			Cache::EXPIRE => time() + 3
		));
		sleep(1);
		$this->assertCached($key, $value);
		sleep(3);
		$this->assertNotCached($key);
	}


	/**
	 * @dataProvider simpleDataProvider
	 * @group Timing
	 */
	public function testSlidingExpiration($key, $value)
	{
		$this->cache->save($key, $value, array(
			Cache::EXPIRE => time() + 2,
			Cache::SLIDING => TRUE
		));

		for ($i = 0; $i < 3; ++$i) {
			sleep(1);
			$this->assertCached($key, $value);
		}

		sleep(3);
		$this->assertNotCached($key);
	}


	/**
	 * @dataProvider simpleDataProvider
	 * @group Timing
	 */
	public function testDependentItems($key, $value)
	{
		$this->cache->save($key, $value, array(
			Cache::ITEMS => array('dependent'),
		));
		$this->assertCached($key, $value);

		$this->cache['dependent'] = 'foo';
		$this->assertNotCached($key);

		$this->cache->save($key, $value, array(
			Cache::ITEMS => array('dependent'),
		));
		$this->assertCached($key, $value);
		sleep(2);
		$this->cache['dependent'] = 'bar';
		$this->assertNotCached($key);

		$this->cache->save($key, $value, array(
			Cache::ITEMS => array('dependent'),
		));
		$this->assertCached($key, $value);

		$this->cache['dependent'] = NULL;
		$this->assertNotCached($key);
	}


	/**
	 * @dataProvider simpleDataProvider
	 * @group Timing
	 */
	public function testFileDependency($key, $value)
	{
		$file = __DIR__ . '/../temp/foo';
		@unlink($file);

		$this->cache->save($key, $value, array(
			Cache::FILES => array(__FILE__, $file)
		));
		$this->assertCached($key, $value);
		file_put_contents($file, 'bar');
		$this->assertNotCached($key);

		$this->cache->save($key, $value, array(
			Cache::FILES => array(__FILE__, $file)
		));
		$this->assertCached($key, $value);
		sleep(2);
		file_put_contents($file, 'baz');
		$this->assertNotCached($key);

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

		$this->assertNotCached(1);
		$this->assertNotCached(2);
		$this->assertCached(3);
		$this->assertCached(4);
	}


	public function testTags()
	{
		$this->cache->save(1, 1, array(Cache::TAGS => array('a', 'b')));
		$this->cache->save(2, 2, array(Cache::TAGS => array('a', 'c')));
		$this->cache->save(3, 3, array(Cache::TAGS => array('b', 'c')));
		$this->cache[4] = 4;

		$this->cache->clean(array(
			Cache::TAGS => 'a',
		));

		$this->assertNotCached(1);
		$this->assertNotCached(2);
		$this->assertCached(3);
		$this->assertCached(4);
	}



	/********************* Tests: complex tests of dependencies *********************/


	/**
	 * @group Complex
	 * @keepCache
	 * @testdox Constant dependencies (save)
	 */
	public function testConstantDependenciesSave()
	{
		if (!defined('ANY_CONST')) {
			define('ANY_CONST', 10);
		}
		$this->cache->save('nette', 'rulez', array(
			Cache::CONSTS => 'ANY_CONST'
		));
		$this->assertCached('nette');
	}

	/**
	 * @group Complex
	 * @depends testConstantDependenciesSave
	 * @runInSeparateProcess
	 * @testdox Constant dependencies (read)
	 */
	public function testConstantDependenciesRead()
	{
		$this->assertNotCached('nette');
	}



	/**
	 * @group Complex
	 * @keepCache
	 * @testdox @serializationVersion dependency (save)
	 */
	public function testSerializationVersionSave()
	{
		require_once __DIR__ . '/Classes/Foo.php';
		$this->cache->save('foo', new Foo());
		$this->assertCached('foo');
	}


	/**
	 * @group Complex
	 * @depends testSerializationVersionSave
	 * @keepCache
	 * @runInSeparateProcess
	 * @testdox @serializationVersion dependency (read same class)
	 */
	public function testSerializationVersionRead()
	{
		require_once __DIR__ . '/Classes/Foo.php';
		$this->assertCached('foo');
	}

	/**
	 * @group Complex
	 * @depends testSerializationVersionRead
	 * @runInSeparateProcess
	 * @testdox @serializationVersion dependency (read versioned class)
	 */
	public function testSerializationVersionReadVersioned()
	{
		require_once __DIR__ . '/Classes/Foo.123.php';
		$this->assertNotCached('foo');
	}
}