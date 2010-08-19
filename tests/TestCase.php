<?php

use Nette\ObjectMixin;

/**
 * All tests should extend this class. It provides Nette\Object functionality.
 * @author	Nette Foundation
 * @package    Nette\Test  
 */ 
abstract class TestCase extends PHPUnit_Framework_TestCase
{

	/**********************************************
	 *	      Helper functions
	 **********************************************/

	/**
	 * Removes specified path inlucing contents of the directory.
	 * @param string
	 */
	protected static function truncateDirectory($path)
	{
		if (is_dir($path)) {
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST) as $entry) {
				if ($entry->isDir() && $entry->getBasename() !== '.' && $entry->getBasename() !== '..') {
					rmdir($entry);
				} else if ($entry->isFile()) {
					unlink($entry);
				}
			}
		}
	}

	/**********************************************
	 *	      Nette\Object functionality
	 **********************************************/
	
	/**
	 * Access to reflection.
	 * @return Nette\Reflection\ClassReflection
	 */
	public static function getReflection()
	{
		return new Nette\Reflection\ClassReflection(get_called_class());
	}

	/**
	 * Call to undefined method.
	 * @param  string  method name
	 * @param  array   arguments
	 * @return mixed
	 * @throws \MemberAccessException
	 */
	public function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
	}

	/**
	 * Call to undefined static method.
	 * @param  string  method name (in lower case!)
	 * @param  array   arguments
	 * @return mixed
	 * @throws \MemberAccessException
	 */
	public static function __callStatic($name, $args)
	{
		$class = get_called_class();
		throw new \MemberAccessException("Call to undefined static method $class::$name().");
	}
	
	/**
	 * Returns property value. Do not call directly.
	 * @param  string  property name
	 * @return mixed   property value
	 * @throws \MemberAccessException if the property is not defined.
	 */
	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}

	/**
	 * Sets value of a property. Do not call directly.
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 * @throws \MemberAccessException if the property is not defined or is read-only
	 */
	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}

	/**
	 * Is property defined?
	 * @param  string  property name
	 * @return bool
	 */
	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}

	/**
	 * Access to undeclared property.
	 * @param  string  property name
	 * @return void
	 * @throws \MemberAccessException
	 */
	public function __unset($name)
	{
		throw new \MemberAccessException("Cannot unset the property {$this->reflection->name}::\$$name.");
	}

}
