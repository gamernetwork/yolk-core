<?php
/*
 * This file is part of Yolk - Gamer Network's PHP Framework.
 *
 * Copyright (c) 2013 Gamer Network Ltd.
 * 
 * Distributed under the MIT License, a copy of which is available in the
 * LICENSE file that was bundled with this package, or online at:
 * https://github.com/gamernetwork/yolk-core
 */

namespace yolk;

final class Yolk {

	protected static $debug;

	protected static $start_time;

	protected static $start_memory;

	/**
	 * Current error handler.
	 * @var array|\Closure
	 */
	protected static $error_handler = array(__CLASS__, 'error');

	protected static $exception_handler;

	protected static $error_page;

	//protected static $helpers = ['isassoc' => ['sss', 'bfbgf']];
	protected static $helpers = [];

	/**
	 * Cannot be instantiated.
	 */
	private function __construct() {}

	/**
	 * Determines if this is a command-line environment.
	 * @return boolean
	 */
	public static function isCLI() {
		return defined('STDIN') && is_resource(STDIN) && (get_resource_type(STDIN) == 'stream');
	}

	/**
	 * Determines if debug mode is enabled.
	 * @return boolean
	 */
	public static function isDebug() {
		return static::$debug;
	}

	/**
	 * Enables or disabled debug mode.
	 * @param boolean  $debug
	 * @return void
	 */
	public static function setDebug( $debug = false ) {
		static::$debug = (bool) $debug;
	}

	/**
	 * Specifies the function to execute in the event of an error being triggered during a call to Yolk::run().
	 * @param \Closure  $callable
	 * @return void
	 */
	public static function setErrorHandler( \Closure $callable = null ) {
		if( !$callable )
			static::$error_handler = array(__CLASS__, 'error');
		else
			static::$error_handler = $callable;
	}

	/**
	 * Specifies the function to execute in the event of an exception being thrown during a call to Yolk::run().
	 * @param \Closure  $callable
	 * @return void
	 */
	public static function setExceptionHandler( \Closure $callable = null ) {
		static::$exception_handler = $callable;
	}

	/**
	 * Specifies the error page to display in the event of an error/exception.
	 * 
	 * @param string  $file
	 * @return void
	 */
	public static function setErrorPage( $file ) {
		static::$error_page = (string) $file;
	}

	/**
	 * Loads the default framework services.
	 * @return \yolk\core\Services
	 */
	public static function loadServices( \yolk\core\Services $services = null ) {

		if( !$services )
			$services = new \yolk\core\Services();

		require __DIR__. '/services.php';

		return $services;

	}

	/**
	 * Executes the specified closure, wrapping it in Yolk's error and exception handling.
	 * @param \Closure  $callable
	 * @return void
	 */
	public static function run( \Closure $callable ) {

		static::$start_time   = microtime(true);
		static::$start_memory = memory_get_usage();

		try {

			// catch fatal errors
			register_shutdown_function(array(__CLASS__, 'shutdown'));

			// set an error handler
			$error_handler = set_error_handler(static::$error_handler);

			// run the code
			$result = $callable();

			// if $error_handler is null then passing it to set_error_handler() will fail on PHP < v5.5
			// so we create an empty error handler thereby causing PHP to run it's own handler.
			if( !$error_handler ) {
				$error_handler = function( $severity, $message, $file, $line ) {
			      return false;
			   };
			}

			// restore the original error handler
			set_error_handler($error_handler);

			return $result;

		}
		catch( \Exception $e ) {
			static::exception($e);
		}

	}

	/**
	 * Shortcut function for running an application.
	 * @param string  $class   the application class
	 * @return void
	 */
	public static function runApp( $class ) {
		return static::run(
			function() use ($class) {
				$app = new $class();
				return $app
					->run();
			}
		);
	}

	/**
	 * Default error handler.
	 * @param int     $severity   the error level (http://php.net/manual/en/errorfunc.constants.php)
	 * @param string  $message
	 * @param string  $file
	 * @param int     $line
	 * @return void
	 */
	public static function error($severity, $message, $file, $line) {

		// if the error was a type hint failure then throw an InvalidArgumentException instead
		if( preg_match('/^Argument (\d+) passed to ([\w\\\\]+)::(\w+)\(\) must be an instance of ([\w\\\\]+), ([\w\\\\]+) given, called in ([\w\s\.\/_-]+) on line (\d+)/', $message, $m) ) {
			throw new \InvalidArgumentException("Argument {$m[1]} to {$m[2]}::{$m[3]}() should be an instance of {$m[4]}, {$m[5]} given", $severity, new \ErrorException($message, 0, $severity, $m[6], $m[7]));
		}
		// convert the error to an exception
		else {
			throw new \ErrorException($message, 0, $severity, $file, $line);
		}

	}

	/**
	 * Default exception handler
	 * @param \Exception  $error
	 * @return void
	 */
	public static function exception( $error ) {

		// run the user defined exception handler if we have one
		if( $handler = static::$exception_handler )
			return $handler($error);
		
		$flags = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;
		$fatal = ($error instanceof \ErrorException) && ($error->getSeverity() & $flags);

		// fatal errors will already have been error_log()'d
		if( !$fatal ) {
			// type hinting error - make sure we give the correct location
			if( ($error instanceof \InvalidArgumentException) && ($error->getPrevious() instanceof \ErrorException) )
				$location = $error->getPrevious()->getFile(). ':'. $error->getPrevious()->getLine();
			else
				$location = $error->getFile(). ':'. $error->getLine();
			error_log(get_class($error). ': '. $error->getMessage(). " [{$location}]");
		}

		// if we're running in a web environment then output the error page
		if( !static::isCLI() ) {
			header("HTTP/1.0 500 Internal Server Error");
			$debug = static::isDebug();
			require static::$error_page ?: __DIR__. '/core/Exception.view.php';
		}

	}

	/**
	 * Shutdown function to catch fatal errors.
	 * @return void
	 */
	public static function shutdown() {
		$flags = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;
		$fatal = ($error = error_get_last()) && ($flags & $error['type']);
		if( $fatal ) {
			static::exception(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
		}
	}

	public static function registerHelpers( $classes ) {

		if( !is_array($classes) )
			$classes = [$classes];

		foreach( $classes as $class ) {

			$class   = new \ReflectionClass($class);
			$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);

			foreach( $methods as $m ) {
				$k = strtolower($m->name);
				if( method_exists(__CLASS__, $m->name) )
					throw new \Exception(sprintf("Helper methods cannot override pre-defined Yolk methods - '%s' is reserved", $m->name));
				elseif( isset(static::$helpers[$k]) && static::$helpers[$k][0] != $class->name )
					throw new \Exception(sprintf("Helper method '%s' already defined in class '%s', duplicate in '%s'", $m->name, static::$helpers[$k][0], $class->name));
				static::$helpers[$k] = [$class->name, $m->name];
			}
			
		}

	}

	public static function __callStatic( $method, array $args = [] ) {

		$k = strtolower($method);

		if( !isset(static::$helpers[$k]) )
			throw new \BadMethodCallException("Unknown helper method '$method'");

		list($class, $method) = static::$helpers[$k];
		
		switch( count($args) ) {
			case 0:
				return $class::$method();
			case 1:
				return $class::$method($args[0]);
			case 2:
				return $class::$method($args[0], $args[1]);
			case 3:
				return $class::$method($args[0], $args[1], $args[2]);
			case 4:
				return $class::$method($args[0], $args[1], $args[2], $args[3]);
			default:
				return call_user_func_array([$class, $method], $args);
		}

	}

}

// EOF