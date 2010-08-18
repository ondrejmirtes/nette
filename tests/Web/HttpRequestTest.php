<?php

namespace Nette\Web;

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class HttpRequestTest extends \TestCase
{

	public function testFiles()
	{
		//set up environment
		$backup = $_FILES;
		$_FILES = array(
			'file1' => array(
				'name' => 'readme.txt',
				'type' => 'text/plain',
				'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
				'error' => 0,
				'size' => 209,
			),
		
			'file2' => array(
				'name' => array(
					2 => 'license.txt',
				),
		
				'type' => array(
					2 => 'text/plain',
				),
		
				'tmp_name' => array(
					2 => 'C:\\PHP\\temp\\php1D5C.tmp',
				),
		
				'error' => array(
					2 => 0,
				),
		
				'size' => array(
					2 => 3013,
				),
			),
		
			'file3' => array(
				'name' => array(
					'y' => array(
						'z' => 'default.htm',
					),
					1 => 'logo.gif',
				),
		
				'type' => array(
					'y' => array(
						'z' => 'text/html',
					),
					1 => 'image/gif',
				),
		
				'tmp_name' => array(
					'y' => array(
						'z' => 'C:\\PHP\\temp\\php1D5D.tmp',
					),
					1 => 'C:\\PHP\\temp\\php1D5E.tmp',
				),
		
				'error' => array(
					'y' => array(
						'z' => 0,
					),
					1 => 0,
				),
		
				'size' => array(
					'y' => array(
						'z' => 26320,
					),
					1 => 3519,
				),
			),
		);
		
		$request = new HttpRequest;

		$this->assertType('Nette\Web\HttpUploadedFile', $request->files['file1']);
		$this->assertType('Nette\Web\HttpUploadedFile', $request->files['file2'][2]);
		$this->assertType('Nette\Web\HttpUploadedFile', $request->files['file3']['y']['z']);
		$this->assertType('Nette\Web\HttpUploadedFile', $request->files['file3'][1]);

		$this->assertArrayNotHasKey('file0', $request->files);
		$this->assertArrayHasKey('file1', $request->files);
		$this->assertNull($request->getFile('file1', 'a'));
		
		$_FILES = $backup;
	}
	
	public function testInvalidEncoding()
	{
		// set up environment
		$invalid = "\x76\xC4\xC5\xBE";
		$controlCharacters = "A\x00B\x80C";
		
		$backupGet = $_GET;
		$_GET = array(
			'$invalid' => $invalid,
			'control' => $controlCharacters,
			$invalid => '1',
			$controlCharacters => '1',
			'array' => array($invalid => '1'),
		);
		
		$backupPost = $_POST;
		$_POST = array(
			'$invalid' => $invalid,
			'control' => $controlCharacters,
			$invalid => '1',
			$controlCharacters => '1',
			'array' => array($invalid => '1'),
		);
		
		$backupCookie = $_COOKIE;
		$_COOKIE = array(
			'$invalid' => $invalid,
			'control' => $controlCharacters,
			$invalid => '1',
			$controlCharacters => '1',
			'array' => array($invalid => '1'),
		);
		
		$backupFiles = $_FILES;
		$_FILES = array(
			$invalid => array(
				'name' => 'readme.txt',
				'type' => 'text/plain',
				'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
				'error' => 0,
				'size' => 209,
			),
			$controlCharacters => array(
				'name' => 'readme.txt',
				'type' => 'text/plain',
				'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
				'error' => 0,
				'size' => 209,
			),
			'file1' => array(
				'name' => $invalid,
				'type' => 'text/plain',
				'tmp_name' => 'C:\\PHP\\temp\\php1D5B.tmp',
				'error' => 0,
				'size' => 209,
			),
		);
		
		
		$request = new HttpRequest;
		
		//unfiltered data
		$this->assertSame($invalid, $request->getQuery('$invalid'));
		$this->assertSame($controlCharacters, $request->getQuery('control'));
		$this->assertSame('1', $request->getQuery($invalid));
		$this->assertSame('1', $request->getQuery($controlCharacters));
		$this->assertSame('1', $request->query['array'][$invalid]);
		
		$this->assertSame($invalid, $request->getPost('$invalid'));
		$this->assertSame($controlCharacters, $request->getPost('control'));
		$this->assertSame('1', $request->getPost($invalid));
		$this->assertSame('1', $request->getPost($controlCharacters));
		$this->assertSame('1', $request->post['array'][$invalid]);
		
		$this->assertSame($invalid, $request->getCookie('$invalid'));
		$this->assertSame($controlCharacters, $request->getCookie('control'));
		$this->assertSame('1', $request->getCookie($invalid));
		$this->assertSame('1', $request->getCookie($controlCharacters));
		$this->assertSame('1', $request->cookies['array'][$invalid]);
		
		$this->assertType('Nette\Web\HttpUploadedFile', $request->getFile($invalid));
		$this->assertType('Nette\Web\HttpUploadedFile', $request->getFile($controlCharacters));
		$this->assertType('Nette\Web\HttpUploadedFile', $request->files['file1']);
		
		// filtered data
		$request->setEncoding('UTF-8');
		
		$this->assertSame("v\xc5\xbe", $request->getQuery('$invalid'));
		$this->assertSame('ABC', $request->getQuery('control'));
		$this->assertNull($request->getQuery($invalid));
		$this->assertNull($request->getQuery($controlCharacters));
		$this->assertArrayNotHasKey($invalid, $request->query['array']);

		$this->assertSame("v\xc5\xbe", $request->getCookie('$invalid'));
		$this->assertSame('ABC', $request->getCookie('control'));
		$this->assertNull($request->getCookie($invalid));
		$this->assertNull($request->getCookie($controlCharacters));
		$this->assertArrayNotHasKey($invalid, $request->cookies['array']);

		$this->assertNull($request->getFile($invalid));
		$this->assertNull($request->getFile($controlCharacters));
		$this->assertType('Nette\Web\HttpUploadedFile', $request->files['file1']);
		$this->assertSame("v\xc5\xbe", $request->files['file1']->name);
		
		$_GET = $backupGet;
		$_POST = $backupPost;
		$_COOKIE = $backupCookie;
		$_FILES = $backupFiles;
	}
	
	public function testRequest()
	{
		// set up environment
		$_SERVER = array(
			'HTTPS' => 'On',
			'HTTP_HOST' => 'nette.org:8080',
			'QUERY_STRING' => 'x param=val.&pa%%72am=val2&param3=v%20a%26l%3Du%2Be)',
			'REMOTE_ADDR' => '192.168.188.66',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/file.php?x param=val.&pa%%72am=val2&param3=v%20a%26l%3Du%2Be)',
			'SCRIPT_FILENAME' => '/public_html/www/file.php',
			'SCRIPT_NAME' => '/file.php',
		);
		
		$request = new HttpRequest;
		$request->addUriFilter('%20', '', PHP_URL_PATH);
		$request->addUriFilter('[.,)]$');
		
		$this->assertSame('GET', $request->getMethod());
		$this->assertTrue($request->isSecured());
		$this->assertSame('192.168.188.66',  $request->getRemoteAddress());
		
		// getUri
		$this->assertSame('/file.php', $request->getUri()->scriptPath);
		$this->assertSame('https', $request->getUri()->scheme);
		$this->assertEmpty($request->getUri()->user);
		$this->assertEmpty($request->getUri()->password);
		$this->assertSame('nette.org', $request->getUri()->host);
		$this->assertSame(8080, $request->getUri()->port);
		$this->assertSame('/file.php', $request->getUri()->path);
		$this->assertSame("x param=val.&pa%\x72am=val2&param3=v a%26l%3Du%2Be", $request->getUri()->query);
		$this->assertEmpty($request->getUri()->fragment);
		$this->assertSame('nette.org:8080', $request->getUri()->authority);
		$this->assertSame('https://nette.org:8080', $request->getUri()->hostUri);
		$this->assertSame('https://nette.org:8080/', $request->getUri()->baseUri);
		$this->assertSame('/', $request->getUri()->basePath);
		$this->assertSame('file.php', $request->getUri()->relativeUri);
		$this->assertSame("https://nette.org:8080/file.php?x param=val.&pa%\x72am=val2&param3=v a%26l%3Du%2Be", $request->getUri()->absoluteUri);
		$this->assertEmpty($request->getUri()->pathInfo);
		
		// getOriginalUri
		$this->assertSame('https', $request->getOriginalUri()->scheme);
		$this->assertEmpty($request->getOriginalUri()->user);
		$this->assertEmpty($request->getOriginalUri()->password);
		$this->assertSame('nette.org', $request->getOriginalUri()->host);
		$this->assertSame(8080, $request->getOriginalUri()->port);
		$this->assertSame('/file.php', $request->getOriginalUri()->path);
		$this->assertSame('x param=val.&pa%%72am=val2&param3=v%20a%26l%3Du%2Be)', $request->getOriginalUri()->query);
		$this->assertEmpty($request->getOriginalUri()->fragment);
		$this->assertSame('val.', $request->getQuery('x_param'));
		$this->assertSame('val2', $request->getQuery('pa%ram'));
		$this->assertSame('v a&l=u+e', $request->getQuery('param3'));
		$this->assertEmpty($request->getPostRaw());
		$this->assertSame('nette.org:8080', $request->headers['host']);
	}

}
