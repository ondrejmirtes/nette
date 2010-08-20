<?php

namespace Nette\Web;

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class UriScriptTest extends \TestCase
{

	public function testModify()
	{
		$uri = new UriScript('http://nette.org:8080/file.php?q=search');
		$uri->path = '/test/';
		$uri->scriptPath = '/test/index.php';

		$this->assertSame('/test/index.php', $uri->scriptPath);
		$this->assertSame('http://nette.org:8080/test/', $uri->baseUri);
		$this->assertSame('/test/', $uri->basePath);
		$this->assertEmpty($uri->relativeUri);
		$this->assertEmpty($uri->pathInfo);
		$this->assertSame('http://nette.org:8080/test/?q=search', $uri->absoluteUri);
	}

	public function testParse()
	{
		$uri = new UriScript('http://nette.org:8080/file.php?q=search');
		$this->assertEmpty($uri->scriptPath);
		$this->assertSame('http://nette.org:8080', $uri->baseUri);
		$this->assertEmpty($uri->basePath);
		$this->assertSame('file.php', $uri->relativeUri);
		$this->assertSame('/file.php', $uri->pathInfo);
	}

}
