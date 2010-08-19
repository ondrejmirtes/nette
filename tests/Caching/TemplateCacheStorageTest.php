<?php

namespace Nette\Templates;

use Nette, Nette\Caching\Cache;

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */
class TemplateCacheStorageTest extends \TestCase
{

	protected $cache;

	protected $key = 'nette';
	protected $template = '<?php echo "Hello World"; ?>';

	public function setUp()
	{
		$this->cache = new Cache(new TemplateCacheStorage(__DIR__ . '/../temp'));
	}

	public function tearDown()
	{
		$this->truncateDirectory(__DIR__ . '/../temp');
	}

	public function testBasicFunctionality()
	{
		$this->assertFalse(isset($this->cache[$this->key]));
		$this->assertNull($this->cache[$this->key]);

		$this->cache[$this->key] = $this->template;
		$this->cache->release();

		$this->assertTrue(isset($this->cache[$this->key]));

		$value = $this->cache[$this->key];
		$this->assertType('array', $value);
		$this->assertArrayHasKey('file', $value);
		$this->assertArrayHasKey('handle', $value);

		ob_start();
		include $value['file'];
		$this->assertEquals(ob_get_clean(), 'Hello World');

		fclose($value['handle']);
	}
}

