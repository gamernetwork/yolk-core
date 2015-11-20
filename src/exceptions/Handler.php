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

namespace yolk\exceptions;

use yolk\Yolk;

class Handler {

	/**
	 * Default error handler - convert errors to ErrorExceptions.
	 * @param int     $severity   the error level (http://php.net/manual/en/errorfunc.constants.php)
	 * @param string  $message
	 * @param string  $file
	 * @param int     $line
	 * @return void
	 */
	public static function error( $severity, $message, $file, $line ) {

		// Latest Twig raises a warning when accessing missing cached views - we can ignore it
		if( preg_match('/filemtime/', $message) )
			return;

		// if the error was a type hint failure then throw an InvalidArgumentException instead
		elseif( preg_match('/^Argument (\d+) passed to ([\w\\\\]+)::(\w+)\(\) must be an instance of ([\w\\\\]+), ([\w\\\\]+) given, called in ([\w\s\.\/_-]+) on line (\d+)/', $message, $m) )
			throw new \InvalidArgumentException("Argument {$m[1]} to {$m[2]}::{$m[3]}() should be an instance of {$m[4]}, {$m[5]} given", $severity, new \ErrorException($message, 0, $severity, $m[6], $m[7]));

		// convert the error to an exception
		throw new \ErrorException($message, 0, $severity, $file, $line);

	}

	/**
	 * Default exception handler
	 * @param \Exception $error
	 * @param string     $error_page   the file containing the error page to include for production web apps
	 * @return void
	 */
	public static function exception( \Exception $error, $error_page, $log = true ) {

		if( $log )
			static::logException($error);

		if( Yolk::isCLI() ) {
			Yolk::dump($error);
		}
		// debug web app
		elseif( Yolk::isDebug() ) {
			require __DIR__. '/error.debug.php';
		}
		// production web app
		else {
			require $error_page;
		}

	}

	/**
	 * Shutdown function to catch fatal errors.
	 * @return void
	 */
	public static function checkFatal() {
		$error = error_get_last();
		if( $error && static::isFatal($error['type']) ) {
			Yolk::exception(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
		}
	}

	/**
	 * Determine if an error code or Exception is a fatal error.
	 * @param  \Exception|integer  $error
	 * @return boolean
	 */
	protected static function isFatal( $error ) {

		if( $error instanceof \Exception )
			return false;

		$fatal = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;

		if( $error instanceof \ErrorException )
			$error = $error->getSeverity();

		return (bool) ($error & $fatal);

	}

	/**
	 * Log an exception to the error log.
	 * @param \Exception $error
	 * @return void
	 */
	protected static function logException( \Exception $error ) {

		// fatal errors will already have been error_log()'d
		if( !static::isFatal($error) ) {
			$location = $error->getFile(). ':'. $error->getLine();
			// type hinting error - make sure we give the correct location
			if( ($error instanceof \InvalidArgumentException) && ($error->getPrevious() instanceof \ErrorException) )
				$location = $error->getPrevious()->getFile(). ':'. $error->getPrevious()->getLine();
			error_log(get_class($error). ': '. $error->getMessage(). " [{$location}]");
		}

	}

}

// EOF