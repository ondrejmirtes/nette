<?php

namespace Nette\Config;

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Config
 * @subpackage UnitTests
 */
abstract class ConfigTest extends \TestCase
{
	/**
	 * Asserts that two configurations are equal.
	 * @param Traversable or array $expected
	 * @param Traversable or array $actual
	 * @param string $subpath for internal use only
	 */
	public function assertConfigEquals($expected, $actual, $subpath = '')
	{
		if (is_array($actual) || $actual instanceof \Countable) {
			$count = count($actual);
		} elseif ($actual instanceof Config) {
			$count = 0;
			foreach ($actual as $v) {
				++$count;
			}
		}
		$this->assertEquals(count($expected), $count, "Number of items in config [$subpath] doesn't match.");
		foreach ($actual as $key => $value) {
			$subkey = ($subpath ? "$subpath." : '') . $key;
			$this->assertTrue(isset($expected[$key]), "Key $subkey was not expected.");
			if (is_array($value) || $value instanceof \Traversable) {
				$this->assertTrue(is_array($expected[$key]) || $expected[$key] instanceof \Traversable);
				$this->assertConfigEquals($expected[$key], $value, $subkey);
			} else {
				$this->assertEquals($expected[$key], $value, "Value for key $subkey was not expected.");
			}
		}
	}
}
