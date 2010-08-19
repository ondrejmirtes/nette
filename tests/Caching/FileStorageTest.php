<?php

namespace Nette\Caching;

require_once __DIR__ . '/CacheStorageTest.php';

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */
class FileStorageTest extends CacheStorageTest
{
	protected function createStorage()
	{
		return new FileStorage(__DIR__ . '/../temp');
	}
}

