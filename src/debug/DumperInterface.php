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

interface DumperInterface {

	const DUMP_TEXT     = 'text';
	const DUMP_HTML     = 'html';
	const DUMP_TERMINAL = 'terminal';

	public static function dump( $var, $output = true );

	public static function dumpNull();

	public static function dumpBoolean( $var );

	public static function dumpInteger( $var );

	public static function dumpFloat( $var );

	public static function dumpString( $str );

	public static function dumpArray( array $arr );

	public static function dumpObject( $obj );

	public static function dumpException( \Exception $e );

	public static function dumpResource( $resource );

}

// EOF