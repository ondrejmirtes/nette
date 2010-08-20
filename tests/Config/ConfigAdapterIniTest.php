<?php

namespace Nette\Config;

require_once __DIR__ . '/ConfigTest.php';

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Config
 * @subpackage UnitTests
 */
class ConfigAdapterIniTest extends ConfigTest
{
	/********************* Test (de)initialization *********************/

	protected function tearDown()
	{
		if (file_exists($file = __DIR__ . '/../temp/tmp.ini')) {
			unlink($file);
		}
	}



	/********************* Test: reading and writing *********************/

	/**
	 * @testdox Config #1: Read
	 */
	public function testRead1()
	{
		$config = Config::fromFile(__DIR__ . '/config/config1.ini');
		$this->assertConfigEquals($this->expectedConfig1, $config);
		return $config;
	}


	/**
	 * @testdox Config #1: Read section
	 */
	public function testReadSection1()
	{
		$config = Config::fromFile(__DIR__ . '/config/config1.ini', 'development');
		$this->assertConfigEquals($this->expectedConfig1['development'], $config);
	}


	/**
	 * @depends testRead1
	 * @testdox Config #1: toArray()
	 */
	public function testToArray1(Config $config)
	{
		$this->assertConfigEquals($this->expectedConfig1, $config->toArray());
	}


	/**
	 * @depends testRead1
	 * @testdox Config #1: Save
	 */
	public function testSave1(Config $config)
	{
		$config->save($actual = __DIR__ . '/../temp/tmp.ini');
		$this->assertFileEquals(__DIR__ . '/expected/ConfigAdapterIni.save.1.ini', $actual, '', TRUE);
	}


	/**
	 * @depends testRead1
	 * @testdox Config #1: Save section
	 */
	public function testSaveSection1(Config $config)
	{
		$config->save($actual = __DIR__ . '/../temp/tmp.ini', 'mysection');
		$this->assertFileEquals(__DIR__ . '/expected/ConfigAdapterIni.saveSection.1.ini', $actual, '', TRUE);
	}


	/**
	 * @testdox Config #2: Read
	 */
	public function testRead2()
	{
		$config = Config::fromFile(__DIR__ . '/config/config2.ini');
		$this->assertConfigEquals($this->expectedConfig2, $config);
		return $config;
	}


	/**
	 * @testdox Config #2: Read section
	 */
	public function testReadSection2()
	{
		$config = Config::fromFile(__DIR__ . '/config/config2.ini', 'development');
		$this->assertConfigEquals($this->expectedConfig2['development'], $config);
		return $config;
	}


	/**
	 * @depends testRead2
	 * @testdox Config #2: toArray()
	 */
	public function testToArray2(Config $config)
	{
		$this->assertConfigEquals($this->expectedConfig2, $config->toArray());
	}


	/**
	 * @depends testRead2
	 * @testdox Config #2: Save
	 */
	public function testSave2(Config $config)
	{
		$config->save($actual = __DIR__ . '/../temp/tmp.ini');
		$this->assertFileEquals(__DIR__ . '/expected/ConfigAdapterIni.save.2.ini', $actual, '', TRUE);
	}


	/**
	 * @depends testRead2
	 * @testdox Config #2: Save section
	 */
	public function testSaveSection2(Config $config)
	{
		$config->save($actual = __DIR__ . '/../temp/tmp.ini', 'mysection');
		$this->assertFileEquals(__DIR__ . '/expected/ConfigAdapterIni.saveSection.2.ini', $actual, '', TRUE);
	}


	/**
	 * @depends testReadSection2
	 * @testdox Config #2: Save modified section
	 */
	public function testSaveSectionModified(Config $config)
	{
		$config->display_errors = TRUE;
		$config->html_errors = FALSE;
		$config->save($actual = __DIR__ . '/../temp/tmp.ini', 'mysection');
		$this->assertFileEquals(__DIR__ . '/expected/ConfigAdapterIni.saveSectionModified.ini', $actual, '', TRUE);
	}



	/********************* Test: error handling *********************/

	/**
	 * @expectedException InvalidStateException
	 * @expectedExceptionMessage Missing parent section [scalar]
	 * @testdox Exception: missing parent
	 */
	public function testExceptionMissingParent()
	{
		Config::fromFile(__DIR__ . '/config/config3.ini');
	}


	/**
	 * @expectedException InvalidStateException
	 * @expectedExceptionMessage Invalid section [scalar.set]
	 * @testdox Exception: invalid section
	 */
	public function testExceptionInvalidParent()
	{
		Config::fromFile(__DIR__ . '/config/config4.ini');
	}


	/**
	 * @expectedException InvalidStateException
	 * @expectedExceptionMessage Invalid key 'date.timezone' in section [set]
	 * @testdox Exception: invalid key
	 */
	public function testExceptionInvalidKey()
	{
		Config::fromFile(__DIR__ . '/config/config5.ini');
	}




	/********************* Internal: expected configuration data *********************/

	private $expectedConfig1 = array(
		'production' => array(
			'webname' => 'the example',
			'database' => array(
				'params' => array(
					'host' => 'db.example.com',
					'username' => 'dbuser',
					'password' => 'secret',
					'dbname' => 'dbname'
				),
				'adapter' => 'pdo_mysql'
			)
		),
		'development' => array(
			'database' => array(
				'params' => array(
					'host' => 'dev.example.com',
					'username' => 'devuser',
					'password' => 'devsecret',
					'dbname' => 'dbname'
				),
				'adapter' => 'pdo_mysql'
			),
			'timeout' => '10',
			'display_errors' => '1',
			'html_errors' => '',
			'items' => array(
				'0' => '10',
				'1' => '20'
			),
			'webname' => 'the example'
		)
	);

	private $expectedConfig2 = array(
		'common' => array(
			'variable' => array(
				'tempDir' => '%appDir%/cache',
				'foo' => '%bar% world',
				'bar' => 'hello',
			),
			'set' => array(
				'date.timezone' => 'Europe/Prague',
				'iconv.internal_encoding' => '%encoding%',
				'mbstring.internal_encoding' => '%encoding%',
				'include_path' => '%appDir%/../_trunk;%appDir%/libs'
			)
		),

		'production' => array(
			'service' => array(
				'Nette-Application-IRouter' => 'Nette\Application\MultiRouter',
				'User' => 'Nette\Security\User',
				'Nette-Autoloader' => 'Nette\AutoLoader'
			),
			'webhost' => 'www.example.com',
			'database' => array(
				'params' => array(
					'host' => 'db.example.com',
					'username' => 'dbuser',
					'password' => 'secret',
					'dbname' => 'dbname'
				),
				'adapter' => 'pdo_mysql'
			),
			'variable' => array(
				'tempDir' => '%appDir%/cache',
				'foo' => '%bar% world',
				'bar' => 'hello',
			),
			'set' => array(
				'date.timezone' => 'Europe/Prague',
				'iconv.internal_encoding' => '%encoding%',
				'mbstring.internal_encoding' => '%encoding%',
				'include_path' => '%appDir%/../_trunk;%appDir%/libs'
			)
		),

		'development' => array(
			'database' => array(
				'params' => array(
					'host' => 'dev.example.com',
					'username' => 'devuser',
					'password' => 'devsecret',
					'dbname' => 'dbname'
				),
				'adapter' => 'pdo_mysql'
			),
			'service' => array(
				'Nette-Application-IRouter' => 'Nette\Application\MultiRouter',
				'User' => 'Nette\Security\User',
				'Nette-Autoloader' => 'Nette\AutoLoader'
			),
			'webhost' => 'www.example.com',
			'variable' => array(
				'tempDir' => '%appDir%/cache',
				'foo' => '%bar% world',
				'bar' => 'hello',
			),
			'set' => array(
				'date.timezone' => 'Europe/Prague',
				'iconv.internal_encoding' => '%encoding%',
				'mbstring.internal_encoding' => '%encoding%',
				'include_path' => '%appDir%/../_trunk;%appDir%/libs'
			),
			'test' => array(
				'host' => 'localhost',
				'params' => array(
					'host' => 'dev.example.com',
					'username' => 'devuser',
					'password' => 'devsecret',
					'dbname' => 'dbname'
				),
				'adapter' => 'pdo_mysql'
			)
		),
		'extra' => array(
			'set' => array(
				'date.timezone' => 'Europe/Paris',
				'iconv.internal_encoding' => '%encoding%',
				'mbstring.internal_encoding' => '%encoding%',
				'include_path' => '%appDir%/../_trunk;%appDir%/libs'
			)
		)
	);
}