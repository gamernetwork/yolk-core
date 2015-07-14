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

// global helper functions (e.g. d() and dd())
require __DIR__.'/bootstrap.php';

Yolk::registerHelpers('yolk\\helpers\\ArrayHelper');
Yolk::registerHelpers('yolk\\helpers\\StringHelper');

class Yolk {

	const DUMP_TEXT     = 'text';
	const DUMP_HTML     = 'html';
	const DUMP_TERMINAL = 'terminal';

	/**
	 * The debug mode flag.
	 * @var boolean
	 */
	protected static $debug;

	/**
	 * Current error handler.
	 * @var callable
	 */
	protected static $error_handler = ['\\yolk\\exceptions\\Handler', 'error'];

	/**
	 * Current exception handler.
	 * @var callable
	 */
	protected static $exception_handler = ['\\yolk\\exceptions\\Handler', 'exception'];

	/**
	 * The error page to display for production web apps.
	 * @var string
	 */
	protected static $error_page;

	/**
	 * Array of helper methods accessable as static method on this class.
	 * @var array
	 */
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
	 * Pretty-print a variable - if running in debug mode.
	 * @param  mixed $var
	 * @param  string $format one of the Yolk::DUMP_* constants
	 * @return void
	 */
	public static function dump( $var, $format = null ) {

		if( !static::isDebug() )
			return;

		$dumpers = [
			static::DUMP_HTML     => '\\yolk\\debug\\HTMLDumper',
			static::DUMP_TERMINAL => '\\yolk\\debug\\TerminalDumper',
			static::DUMP_TEXT     => '\\yolk\\debug\\TextDumper',
		];

		// auto-detect format if unknown or none specified
		if( !isset($dumpers[$format]) )
			$format = static::isCLI() ? static::DUMP_TERMINAL : static::DUMP_HTML;

		$dumpers[$format]::dump($var);

	}

	/**
	 * Register one or multiple static helper classes.
	 * Static methods defined within each class will become statically callable
	 * via the Yolk object.
	 * .e.g yolk\helpers\ArrayHelper::sum() -> yolk\Yolk::sum()
	 * @param  string|array $classes
	 */
	public static function registerHelpers( $classes ) {

		if( !is_array($classes) )
			$classes = [$classes];

		foreach( $classes as $class ) {

			$class   = new \ReflectionClass($class);
			$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);

			foreach( $methods as $m ) {
				static::addHelperMethod($class->name, $m->name);
			}
			
		}

	}

	/**
	 * Register a single static method as a helper.
	 * @param string $class
	 * @param string $method
	 */
	public static function addHelperMethod( $class, $method ) {

		$k = strtolower($method);

		if( method_exists(__CLASS__, $method) )
			throw new \Exception(sprintf("Helper methods cannot override pre-defined Yolk methods - '%s' is reserved", $method));
		elseif( isset(static::$helpers[$k]) && static::$helpers[$k][0] != $class )
			throw new \Exception(sprintf("Helper method '%s' already defined in class '%s', duplicate in '%s'", $method, static::$helpers[$k][0], $class));

		static::$helpers[$k] = [$class, $method];

	}

	/**
	 * Executes the specified closure, wrapping it in Yolk's error and exception handling.
	 * @param \Closure  $callable
	 * @return void
	 */
	public static function run( Callable $callable ) {

		try {

			// catch fatal errors
			register_shutdown_function(['\\yolk\\exceptions\\Handler', 'checkFatal']);

			// use our error handler
			$error_handler = set_error_handler(static::$error_handler);

			$args = func_get_args();
			array_shift($args);

			$result = call_user_func_array($callable, $args);

			// restore the original error handler
			// if $error_handler is null then passing it to set_error_handler() will fail on PHP < v5.5
			// so we create an empty error handler thereby causing PHP to run it's own handler.
			set_error_handler($error_handler ?: function() { return false; });

			return $result;

		}
		catch( \Exception $e ) {
			static::exception($e);
		}

	}

	/**
	 * Specifies the function to execute in the event of an error being triggered during a call to Yolk::run().
	 * @param callable $handler
	 * @return void
	 */
	public static function setErrorHandler( callable $handler = null ) {
		static::$error_handler = $handler ?: ['\\yolk\\exceptions\\Handler', 'error'];
	}

	/**
	 * Specifies the function to execute in the event of an exception being thrown during a call to Yolk::run().
	 * @param callable $handler
	 * @return void
	 */
	public static function setExceptionHandler( callable $handler = null ) {
		static::$exception_handler = $handler ?: ['\\yolk\\exceptions\\Handler', 'exception'];
	}

	/**
	 * Call the registered exception handler.
	 * @param  \Exception $error
	 * @return void
	 */
	public static function exception( \Exception $error ) {
		return call_user_func(
			static::$exception_handler,
			$error,
			static::$error_page ?: __DIR__. '/exceptions/error.php'
		);
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

	public static function __callStatic( $method, array $args = [] ) {

		$k = strtolower($method);

		if( !isset(static::$helpers[$k]) )
			throw new \BadMethodCallException("Unknown helper method '$method'");

		return call_user_func_array(static::$helpers[$k], $args);

	}

}

// EOF