<?php

/**
 * My Application bootstrap file.
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */


use Nette\Debug;
use Nette\Environment;
use Nette\Application\Route;
use Nette\Application\SimpleRouter;



// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require LIBS_DIR . '/Nette/loader.php';



// Step 2: Configure environment
// 2a) enable Nette\Debug for better exception and error visualisation
Debug::enable();

// 2b) load configuration from config.ini file
Environment::loadConfig();



// Step 3: Configure application
// 3a) get and setup a front controller
$application = Environment::getApplication();
$application->errorPresenter = 'Error';
//$application->catchExceptions = TRUE;



// Step 4: Setup application router
$router = $application->getRouter();

// mod_rewrite detection
// FastCGI prefixes environment variables
if (isset($_SERVER['NETTE_MOD_REWRITE']) || isset($_SERVER['REDIRECT_NETTE_MOD_REWRITE'])) {
	$router[] = new Route('index.php', array(
		'presenter' => 'Homepage',
		'action' => 'default',
	), Route::ONE_WAY);
	
	$router[] = new Route('<presenter>/<action>/<id>', array(
		'presenter' => 'Homepage',
		'action' => 'default',
		'id' => NULL,
	));
} else {
	$router[] = new SimpleRouter(array(
		'presenter' => 'Homepage',
		'action' => 'default',
	));
}



// Step 5: Run the application!
$application->run();
