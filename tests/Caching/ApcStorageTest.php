<?php

namespace Nette\Caching;

require_once __DIR__ . '/CacheStorageTest.php';

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */
class ApcStorageTest extends CacheStorageTest
{
	public function setUp()
	{
		if (!ApcStorage::isAvailable()) {
			$this->markTestSkipped('ApcStorage is not available.');
		}

		parent::setUp();
	}

	protected function createStorage()
	{
		return new ApcStorage();
	}

	public function clearCache()
	{
		apc_clear_cache('user');

		//parent::clearCache();
	}

	/**
	 * @dataProvider simpleDataProvider
	 * @group Timing
	 */
	public function testDependentItems($key, $value)
	{
		$this->markTestSkipped('Dependent items are not supported by ApcStorage.');
	}

	/**
	 * @group Complex
	 * @testdox Constant dependencies (save)
	 */
	public function testConstantDependenciesSave()
	{
		$this->markTestSkipped('APC cache is not shared between processes.');
	}

	/**
	 * @group Complex
	 * @testdox @serializationVersion dependency (save)
	 */
	public function testSerializationVersionSave()
	{
		$this->markTestSkipped('APC cache is not shared between processes.');
	}
}

