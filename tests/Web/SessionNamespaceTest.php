<?php

namespace Nette\Web;

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class SessionNamespaceTest extends \TestCase
{

	public function testBasicBehaviour()
	{
		$session = new Session;
		$namespace = $session->getNamespace('one');
		$namespace->a = 'apple';
		$namespace->p = 'pear';
		$namespace['o'] = 'orange';
		
		// iterator
		foreach($namespace as $key => $value) {
			switch($key) {
				case 'a':
					$this->assertEquals('apple', $value);
					break;
				case 'p':
					$this->assertEquals('pear', $value);
					break;
				case 'o':
					$this->assertEquals('orange', $value);
					break;
				default:
					$this->fail('Namespace iterator should not have any other keys.');
			}
		}
		
		$this->assertTrue(isset($namespace['p']));
		$this->assertTrue(isset($namespace->o));
		$this->assertFalse(isset($namespace->undefined));
		
		unset($namespace['a']);
		unset($namespace->p);
		unset($namespace->o);
		unset($namespace->undef);
		
		$this->assertEmpty(http_build_query($namespace->getIterator()));
	}
	
	public function testRemove()
	{
		$session = new Session;
		$namespace = $session->getNamespace('three');
		$namespace->a = 'apple';
		$namespace->p = 'papaya';
		$namespace['c'] = 'cherry';

		$this->assertEquals('apple', $namespace->a);
		$this->assertEquals('papaya', $namespace->p);
		$this->assertEquals('cherry', $namespace['c']);
		$namespace->remove();
		$this->assertEmpty(http_build_query($namespace->getIterator()));
	}
	
	public function testSeparated()
	{
		$session = new Session;
		$namespace1 = $session->getNamespace('namespace1');
		$namespace1b = $session->getNamespace('namespace1');
		$namespace2 = $session->getNamespace('namespace2');
		$namespace2b = $session->getNamespace('namespace2');
		$namespace3 = $session->getNamespace('default');
		$namespace3b = $session->getNamespace('default');
		$namespace1->a = 'apple';
		$namespace2->a = 'pear';
		$namespace3->a = 'orange';
		
		// Test session improperly shared namespaces
		$this->assertNotSame($namespace1->a, $namespace2->a);
		
		$this->assertNotSame($namespace1->a ,$namespace3->a);
		$this->assertNotSame($namespace2->a, $namespace3->a);
		$this->assertSame($namespace1->a, $namespace1b->a);
		$this->assertSame($namespace2->a, $namespace2b->a);
		$this->assertsame($namespace3->a, $namespace3b->a);
	}
	
	public function testUndefined()
	{
		$session = new Session;
		$namespace = $session->getNamespace('one');
		$this->assertFalse(isset($namespace->undefined));
		$this->assertNull($namespace->undefined, 'Getting value of not existing key.');
		$this->assertEmpty('', http_build_query($namespace->getIterator()));
	}
	
	public function testExpirationOfWholeNamespace()
	{
		$session = new Session;
		$namespace = $session->getNamespace('expire');
		$namespace->a = 'apple';
		$namespace->p = 'pear';
		$namespace['o'] = 'orange';
		$namespace->setExpiration('+ 1 second');
		
		$session->close();
		sleep(2);
		$session->start();
		
		$namespace = $session->getNamespace('expire');
		$this->assertEmpty(http_build_query($namespace->getIterator()));
	}
	
	public function testExpirationOfOneKey()
	{
		$session = new Session;
		$namespace = $session->getNamespace('expireSingle');
		$namespace->setExpiration('+ 1 second', 'g');
		$namespace->g = 'guava';
		$namespace->p = 'plum';
		
		$session->close();
		sleep(2);
		$session->start();
		
		$namespace = $session->getNamespace('expireSingle');
		$this->assertEquals('plum', $namespace->p);
		$this->assertNull($namespace->g);
	}

}
