<?php

namespace Nette\Web;

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class UriTest extends \TestCase
{

	public function testCanonicalize()
	{
		$uri = new Uri('http://hostname/path?arg=value&arg2=v%20a%26l%3Du%2Be');
		$this->assertSame('arg=value&arg2=v%20a%26l%3Du%2Be', $uri->query);

		$uri->canonicalize();
		$this->assertSame('arg=value&arg2=v a%26l%3Du%2Be', $uri->query);
	}

	public function testFileScheme()
	{
		$uri = new Uri('file://localhost/D:/dokumentace/rfc3986.txt');
		$this->assertSame('file://localhost/D:/dokumentace/rfc3986.txt', (string) $uri);
		$this->assertSame('file', $uri->scheme);
		$this->assertEmpty($uri->user);
		$this->assertEmpty($uri->password);
		$this->assertSame('localhost', $uri->host);
		$this->assertNull($uri->port);
		$this->assertSame('/D:/dokumentace/rfc3986.txt', $uri->path);
		$this->assertEmpty($uri->query);
		$this->assertEmpty($uri->fragment);

		$uri = new Uri('file:///D:/dokumentace/rfc3986.txt');
		$this->assertSame('file://D:/dokumentace/rfc3986.txt', (string) $uri);
		$this->assertSame('file', $uri->scheme);
		$this->assertEmpty($uri->user);
		$this->assertEmpty($uri->password);
		$this->assertEmpty($uri->host);
		$this->assertNull($uri->port);
		$this->assertSame('D:/dokumentace/rfc3986.txt', $uri->path);
		$this->assertEmpty($uri->query);
		$this->assertEmpty($uri->fragment);
	}

	public function testFtpScheme()
	{
		$uri = new Uri('ftp://ftp.is.co.za/rfc/rfc3986.txt');

		$this->assertSame('ftp', $uri->scheme);
		$this->assertEmpty($uri->user);
		$this->assertEmpty($uri->password);
		$this->assertSame('ftp.is.co.za', $uri->host);
		$this->assertSame(21, $uri->port);
		$this->assertSame('/rfc/rfc3986.txt', $uri->path);
		$this->assertEmpty($uri->query);
		$this->assertEmpty($uri->fragment);
		$this->assertSame('ftp.is.co.za', $uri->authority);
		$this->assertSame('ftp://ftp.is.co.za', $uri->hostUri);
		$this->assertSame('ftp://ftp.is.co.za/rfc/rfc3986.txt', $uri->absoluteUri);
	}

	public function testHttpScheme()
	{
		$uri = new Uri('http://username:password@hostname:60/path?arg=value#anchor');

		$this->assertSame('http://hostname:60/path?arg=value#anchor', (string) $uri);
		$this->assertSame('http', $uri->scheme);
		$this->assertSame('username', $uri->user);
		$this->assertSame('password', $uri->password);
		$this->assertSame('hostname', $uri->host);
		$this->assertSame(60, $uri->port);
		$this->assertSame('/path', $uri->path);
		$this->assertSame('arg=value', $uri->query);
		$this->assertSame('anchor', $uri->fragment);
		$this->assertSame('hostname:60', $uri->authority);
		$this->assertSame('http://hostname:60', $uri->hostUri);
		$this->assertSame('http://hostname:60/path?arg=value#anchor', $uri->absoluteUri);
	}

	public function testIsEqual()
	{
		$uri = new Uri('http://exampl%65.COM?text=foo%20bar+foo&value');
		$uri->canonicalize();
		$this->assertTrue($uri->isEqual('http://example.com/?text=foo+bar%20foo&value'));
		$this->assertTrue($uri->isEqual('http://example.com/?value&text=foo+bar%20foo'));
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Malformed or unsupported URI ':'.
	 */
	public function testMalformedUri()
	{
		$uri = new Uri(':');
	}

	public function testQuery()
	{
		$uri = new Uri('http://hostname/path?arg=value');
		$this->assertSame('arg=value', $uri->query);

		$uri->appendQuery(NULL);
		$this->assertSame('arg=value', $uri->query);

		$uri->appendQuery(array(NULL));
		$this->assertSame('arg=value', $uri->query);

		$uri->appendQuery('arg2=value2');
		$this->assertSame('arg=value&arg2=value2', $uri->query);

		$uri->appendQuery(array('arg3' => 'value3'));
		$this->assertSame('arg=value&arg2=value2&arg3=value3', $uri->query);

		$uri->setQuery(array('arg3' => 'value3'));
		$this->assertSame('arg3=value3', $uri->query);
	}

}
