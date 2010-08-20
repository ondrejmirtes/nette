<?php

namespace Nette\Web;

use Nette\Security\IAuthenticator;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;

/**
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class UserAuthenticationTest extends \TestCase
{

	/**
	 * @var Nette\Web\User
	 */
	private $user;

	/**
	 * @var bool
	 */
	private $onLoggedInCalled = FALSE;

	/**
	 * @var bool
	 */
	private $onLoggedOutCalled = FALSE;

	protected function setUp()
	{
		$_COOKIE = array();

		$this->user = new User;

		$this->onLoggedInCalled = FALSE;
		$this->onLoggedOutCalled = FALSE;
	}

	public function onLoggedIn($user)
	{
		$this->onLoggedInCalled = TRUE;
	}

	public function onLoggedOut($user)
	{
		$this->onLoggedOutCalled = TRUE;
	}

	public function testNewUser()
	{
		$this->assertFalse($this->user->isLoggedIn(), 'Failed User::isLoggedin() while logged out test.');
		$this->assertNull($this->user->getIdentity(), 'Failed User::getIdentity() while logged out test.');
		$this->assertNull($this->user->getId(), 'Failed User::getId() while logged out test.');
	}

	/**
	 * @expectedException InvalidStateException
	 * @expectedExceptionMessage Service 'Nette\Security\IAuthenticator' not found.
	 */
	public function testMissingAuthenticator()
	{
		$this->user->login('jane', '');
	}

	/**
	 * @expectedException Nette\Security\AuthenticationException
	 * @expectedExceptionMessage Unknown user.
	 */
	public function testUnknownUser()
	{
		$handler = new AuthenticationTestHandler;
		$this->user->setAuthenticationHandler($handler);
		$this->user->login('jane', '');
	}

	/**
	 * @expectedException Nette\Security\AuthenticationException
	 * @expectedExceptionMessage Password does not match.
	 */
	public function testBadPassword()
	{
		$handler = new AuthenticationTestHandler;
		$this->user->setAuthenticationHandler($handler);
		$this->user->login('john', 'foo');
	}

	public function testRightLogin()
	{
		$handler = new AuthenticationTestHandler;
		$this->user->setAuthenticationHandler($handler);
		$this->user->login('john', 'xxx');
		$this->assertTrue($this->user->isLoggedIn());
		$this->assertType('Nette\Security\Identity', $this->user->getIdentity());
		$this->assertSame('John Doe', $this->user->getIdentity()->getId());
		$this->assertContains('admin', $this->user->getIdentity()->getRoles());

		return $this->user;
	}

	/**
	 * @depends testRightLogin
	 */
	public function testLogout(User $user)
	{
		// logout without clearing identity
		$user->logout(FALSE);
		$this->assertFalse($user->isLoggedIn());
		$this->assertType('Nette\Security\Identity', $user->getIdentity());
		$this->assertSame('John Doe', $user->getIdentity()->getId());
		$this->assertContains('admin', $user->getIdentity()->getRoles());

		// clear identity
		$user->logout(TRUE);
		$this->assertFalse($user->isLoggedIn());
		$this->assertNull($user->getIdentity());
	}

	public function testNamespaces()
	{
		$handler = new AuthenticationTestHandler;
		$this->user->setAuthenticationHandler($handler);
		$this->user->login('john', 'xxx');
		$this->assertTrue($this->user->isLoggedin());

		$this->user->setNamespace('other');
		$this->assertFalse($this->user->isLoggedIn());
		$this->assertNull($this->user->getIdentity());
	}

	public function testCallbacks()
	{
		$this->user->onLoggedIn[] = callback($this, 'onLoggedIn');
		$this->user->onLoggedOut[] = callback($this, 'onLoggedOut');
		$this->assertFalse($this->onLoggedInCalled);
		$this->assertFalse($this->onLoggedOutCalled);

		$handler = new AuthenticationTestHandler;
		$this->user->setAuthenticationHandler($handler);
		$this->user->login('john', 'xxx');
		$this->assertTrue($this->onLoggedInCalled);
		$this->assertFalse($this->onLoggedOutCalled);

		$this->user->logout();
		$this->assertTrue($this->onLoggedInCalled);
		$this->assertTrue($this->onLoggedOutCalled);
	}
}


/**
 * Sample authentication handler.
 * @author     Nette Foundation
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class AuthenticationTestHandler implements IAuthenticator
{

	/*
	 * @param  array
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	function authenticate(array $credentials)
	{
		if ($credentials[self::USERNAME] !== 'john') {
			throw new AuthenticationException('Unknown user.', self::IDENTITY_NOT_FOUND);
		}

		if ($credentials[self::PASSWORD] !== 'xxx') {
			throw new AuthenticationException('Password does not match.', self::INVALID_CREDENTIAL);
		}

		return new Identity('John Doe', 'admin');
	}

}
