<?php

/**
 * Test environment initialization.
 *
 * @author     Nette Foundation
 * @package    Nette\Test
 */

use Nette\Debug;

/**
 * Check PHPUnit version (3.5.0 or higher required).
 * New versions load PHPUnit framework automatically. 
 */
if (!class_exists('PHPUnit_Framework_TestCase') || (float) PHPUnit_Runner_Version::id() < 3.5) {
	if (@include_once 'PHPUnit/Framework.php') {
		die(sprintf("\nPHPUnit 3.5.0 or higher required, you have %s.\n", PHPUnit_Runner_Version::id()));
	} else {
		die("\nPHPUnit 3.5.0 or higher required, none installed.\n");
	}
}

/**
 * Nette Framework (with NetteLoader).
 */ 
require_once(__DIR__ . '/../Nette/loader.php');

/**
 * Nette\Debug error handling.
 */ 
Debug::enable();

/**
 * Load TestCase class
 */
require_once(__DIR__ . '/TestCase.php');
