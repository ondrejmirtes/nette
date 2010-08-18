<?php

/**
 * Test environment initialization.
 *
 * @author     Nette Foundation
 * @package    Nette\Test
 */

use Nette\Debug;

/**
 * Nette Framework (with NetteLoader).
 */ 
require_once(dirname(__FILE__) . '/../Nette/loader.php');

/**
 * Nette\Debug error handling.
 */ 
Debug::enable();

/**
 * Load TestCase class
 */
require_once(dirname(__FILE__) . '/TestCase.php');
