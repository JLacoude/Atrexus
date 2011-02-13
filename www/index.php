<?php
/**
 * Application entry point
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */

// Configure autoload
// Modify include path to add class and interfaces dir. Can be removed if added in php.ini
set_include_path(get_include_path().PATH_SEPARATOR.
		 __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.PATH_SEPARATOR.
		 __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'interfaces'.DIRECTORY_SEPARATOR.PATH_SEPARATOR.
		 __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR);
spl_autoload_extensions('.php');
spl_autoload_register();

// Send headers
header("Content-type: text/html; charset=UTF-8");
// Register error and exception handlers
set_exception_handler(array('Debug', 'exceptionHandler'));
set_error_handler(array('Debug', 'errorHandler'));

// Load dependency container
$DI = new DependencyInjectionContainer(__DIR__.'/../config/app.ini');

// Manage page parameters to load the desired controller
$controller = StringTools::noPathFilterInput(INPUT_GET, 'controller');
if(!empty($controller)){
  $controller = $controller;
}
else{
  $controller = 'Main';
}
$ctrl = new $controller($DI);
$ctrl->execute();
$ctrl->loadTemplate();
