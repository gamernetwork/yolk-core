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

namespace yolk\helpers;

class DebugHelper {

	/**
	 * Helpers cannot be instantiated.
	 */
	private function __construct() {}

	/**
	 * Returns a simple string representation of a variable for use in debug/error messages.
	 *
	 * @param  mixed   $var
	 * @return string
	 */
	public static function info( $var ) {
		if( is_null($var) ) {
			$info = 'null';
		}
		elseif( is_scalar($var) ) {
			ob_start();
			var_dump($var);
			$info = ob_get_clean();
		}
		elseif( is_array($var) ) {
			$info = 'array('. count($var). ')';
		}
		elseif( is_object($var) ) {
			$info = '\\'. get_class($var);
		}
		elseif( is_resource($var) ) {
			$info = 'resource('. get_resource_type($var). ')';
		}
		// should never get here
		else {
			$info = gettype($var);
		}
		return trim($info);
	}

	/**
	 * Pretty variable dump function.
	 * @param mixed    $var
	 * @param boolean  $echo   if false the function will return the output as a string
	 */
	public static function dump( $var, $echo = true ) {

		if( !Yolk::isDebug() )
			return '';

		static $depth = 0;

		$depth++;

		if( is_array($var) ) {
			$output = "array {\n";
			foreach( $var as $k => $v ) {
				$output .= str_repeat("\t", $depth). "[{$k}] => ". static::dump($v, false);
			}
			$output .= str_repeat("\t", $depth - 1). "}\n";
			$var = $output;
		}
		elseif( is_object($var) ) {
			if( $var instanceof \Exception ) {
				$output = get_class($var). " {\n";
				$output .= "\t[code] => ". $var->getCode(). "\n";
				$output .= "\t[message] => ". $var->getMessage(). "\n";
				$output .= "\t[file] => ". $var->getFile(). "\n";
				$output .= "\t[line] => ". $var->getLine(). "\n";
				$trace = preg_replace(
					"/\n([\s]*)/",
					"\n\t\t\\1",
					preg_replace(
						"/\n\n/",
						"\n\n\t\t",
						static::trace(
							$var->getTrace(),
							false
						)
					)
				);
				$output .= "\t[trace] => \n\t\t{$trace}";
			}
			elseif( ($var instanceof \Iterator) && ($depth <= 4 ) ) {
				$output = get_class($var). " {\n";
				foreach( $var as $k => $v ) {
					$output .= str_repeat("\t", $depth). "[{$k}] => ". static::dump($v, false);
				}
				$output .= str_repeat("\t", $depth - 1). "}\n";
			}
			else {
				$output = get_class($var). "\n";
			}
			$var = $output;
		}
		else {
			ob_start();
			var_dump($var);
			$var = ob_get_clean();   
		}

		$depth--;

		if( $echo )
			echo trim($var), "\n";
		else
			return $var;

	}

	/**
	 * Pretty print a stack trace generated by debug_backtrace() or Exception::getTrace().
	 * @param array    $trace  a stack trace, if not specified debug_backtrace() will be called
	 * @param boolean  $echo   if false the function will return the output as a string
	 */
	public static function trace( $trace = null, $echo = true ) {

		if( !Yolk::isDebug() )
			return '';

		if( !$trace ) {
			$trace = debug_backtrace();
			array_shift($trace);
		}

		$i   = 0;
		$str = "";

		foreach( $trace as $frame ) {

			$str .= "#".str_pad($i++, 2)." ";

			if( isset($frame['class']) )
				$str .= "{$frame['class']}{$frame['type']}";

			$str .= $frame['function']. "(";

			if( $frame['args'] ) {
				$str .= "\n";
				foreach( $frame['args'] as $j => $arg ) {
					$str .= "\t[{$j}] => ". str_replace("\n", "\n\t", trim(static::dump($arg, false))). "\n";
				}
				$str .= "    ";
			}
			$str .= ")\n";

			if( isset($frame['file']) )
				$str .= "    {$frame['file']} (Line: {$frame['line']})\n";

			$str .= "\n";

		}

		if( $echo )
			echo $str;
		else
			return $str;

	}

}

// EOF