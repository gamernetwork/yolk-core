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

class TextDumper extends AbstractDumper {

	protected static $depth = 0;

	public static function dumpNull() {
		return 'null';
	}

	public static function dumpBoolean( $var ) {
		return sprintf('bool(%s)', $var ? 'true' : 'false');
	}

	public static function dumpInteger( $var ) {
		return "int({$var})";
	}

	public static function dumpFloat( $var ) {
		return "float({$var})";
	}

	public static function dumpString( $str ) {
		$enc = mb_detect_encoding($str);
		$enc = ($enc == 'ASCII') ? '' : "; $enc";
		return sprintf('string(%d%s) "%s"', strlen($str), $enc, $str);
	}

	public static function dumpArray( array $arr ) {

		static::$depth++;

		$item = sprintf("array(%d) {\n", count($arr));
		foreach( $arr as $k => $v ) {
			$item .= sprintf("%s[%s] => %s\n", str_repeat("\t", static::$depth), $k, static::dump($v, false));
		}
		$item .= str_repeat("\t", static::$depth - 1). "}";

		static::$depth--;

		return $item;

	}

	public static function dumpObject( $obj ) {

		if( $obj instanceof \Exception )
			return static::dumpException($obj);

	}

	public static function dumpException( \Exception $e ) {

		$item = get_class($e);

		$meta = [
			'message'  => $e->getMessage(),
			'code'     => $e->getCode(),
			'file'     => $e->getFile(),
			'line'     => $e->getLine(),
			'trace'    => [],
			'previous' => $e->getPrevious(),
		];

		if( $e instanceof \ErrorException ) {
			$lookup = [
				E_ERROR             => 'ERROR',
				E_WARNING           => 'WARNING',
				E_PARSE             => 'PARSE',
				E_NOTICE            => 'NOTICE',
				E_CORE_ERROR        => 'CORE_ERROR',
				E_CORE_WARNING      => 'CORE_WARNING',
				E_COMPILE_ERROR     => 'COMPILE_ERROR',
				E_COMPILE_WARNING   => 'COMPILE_WARNING',
				E_USER_ERROR        => 'USER_ERROR',
				E_USER_WARNING      => 'USER_WARNING',
				E_USER_NOTICE       => 'USER_NOTICE',
				E_STRICT            => 'STRICT',
				E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
				E_DEPRECATED        => 'DEPRECATED',
				E_USER_DEPRECATED   => 'USER_DEPRECATED',
			];
			$meta = array_merge([
				'severity' => $lookup[$e->getSeverity()],
			], $meta);
		}

		foreach( $e->getTrace() as $i => $frame ) {
			$line = '';
			isset($frame['class']) && $line .= $frame['class']. $frame['type'];
			$line .= $frame['function']. '()';

			if( isset($frame['file']) ) {
				$line .= ' ['. $frame['file'];
				if( isset($frame['line']) )
					$line .= ':'. $frame['line'];
				$line .= ']';
			}

			$meta['trace'][] = $line;
		}

		$item .= static::dumpMeta($meta);

		return $item;

	}

	public static function dumpResource( $resource ) {

		$type = get_resource_type($resource);

		$item = (string) $resource;
		$item = sprintf("resource(%s; %s)", substr($item, strpos($item, '#')), $type);

		// try and get some additional info about the resource
		switch( $type ) {
			case 'stream':
            	$meta = stream_get_meta_data($resource);
				break;

			case 'curl':
            	$meta = curl_getinfo($resource);
				break;

			default:
				$meta = [];
		}

		if( $meta ) {
			$item .= static::dumpMeta($meta);
		}

		return $item;

	}

	protected static function dumpMeta( $meta ) {

		static::$depth++;

		$width = max(array_map('strlen', array_keys($meta))) + 1;

		$item = " {\n";
		foreach( $meta as $k => $v ) {
			$item .= sprintf("%s%s: %s\n", str_repeat("\t", static::$depth), str_pad(ucwords(str_replace('_', ' ', $k)), $width) , static::dump($v, false));
		}
		$item .= str_repeat("\t", static::$depth - 1). "}";

		static::$depth--;

		return $item;

	}

}

// EOF