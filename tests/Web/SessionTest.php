<?php

namespace Nette\Web;

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class SessionTest extends \TestCase
{

	public function testNamespaces()
	{
		$session = new Session;
		$this->assertFalse($session->hasNamespace('trees'), 'Session::hasNamespace() should have returned FALSE for not existing namespace.');
		
		$namespace = $session->getNamespace('trees');
		$this->assertFalse($session->hasNamespace('trees'), 'Session::hasNamespace() should have returned FALSE for a namespace with no keys set.');
		
		$namespace->hello = 'world';
		$this->assertTrue($session->hasNamespace('trees'), 'Session::hasNamespace() should have returned TRUE for a namespace with keys set.');
		
		$namespace = $session->getNamespace('default');
		$this->assertType('Nette\Web\SessionNamespace', $namespace);
	}
	
	public function testRegenerateId()
	{
		$session = new Session;
		if (!$session->isStarted()) {
			$session->start();
		}
		
		$oldId = $session->getId();
		$session->regenerateId();
		$newId = $session->getId();
		$this->assertNotEquals($oldId, $newId);
	}

}
