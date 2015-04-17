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

namespace yolk\debug;

abstract class AbstractDumper implements DumperInterface {

	private function __construct() {}

	public static function dump( $var, $output = true ) {

		if( $var === null ) {
			$item = static::dumpNull();
		}
		elseif( is_bool( $var ) ) {
			$item = static::dumpBoolean($var);
		}
		elseif( is_integer( $var ) ) {
			$item = static::dumpInteger($var);
		}
		elseif( is_float( $var ) ) {
			$item = static::dumpFloat($var);
		}
		elseif( is_string( $var ) ) {
			$item = static::dumpString($var);
		}
		elseif( is_array($var) ) {
			$item = static::dumpArray($var);
		}
		elseif( is_object($var) ) {
			$item = static::dumpObject($var);
		}
		elseif( is_resource($var) ) {
			$item = static::dumpResource($var);
		}
		else {
			ob_start();
			var_dump($var);
			$item = ob_get_clean();   
		}

		if( $output )
			static::output($item);

		return $item;

	}

	protected static function output( $item ) {
		echo $item, "\n";
	}

}

// EOF