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

		$dumpers = [
			'is_null'     => 'dumpNull',
			'is_bool'     => 'dumpBoolean',
			'is_integer'  => 'dumpInteger',
			'is_float'    => 'dumpFloat',
			'is_string'   => 'dumpString',
			'is_array'    => 'dumpArray',
			'is_object'   => 'dumpObject',
			'is_resource' => 'dumpResource',
		];

		$item = false;

		foreach( $dumpers as $test => $dumper ) {
			if( $test($var) ) {
				$item = static::$dumper($var);
				break;
			}
		}

		if( !$item ) {
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