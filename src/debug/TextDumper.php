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

use yolk\contracts\support\Dumpable;

class TextDumper extends AbstractDumper {

	protected static $depth = 0;

	protected static $stack = [];

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
		$enc = mb_detect_encoding($str, ['UTF-8', 'WINDOWS-1252', 'ISO-8859-1', 'ASCII'], true);
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

		if( $item = static::recursionCheck($obj) ) {
			return $item;
		}
		elseif( $obj instanceof Dumpable ) {
			$item = $obj->dump(get_called_class(), static::$depth + 1);
			if( $item )
				return $item;
		}
		elseif( $obj instanceof \Exception )
			return static::dumpException($obj);

		static::$stack[] = $obj;

		static::$depth++;

		$item = get_class($obj). " {\n";

		$item .= static::dumpObjectProperties($obj);

		$item .= str_repeat("\t", static::$depth - 1). "}";

		static::$depth--;

		array_pop(static::$stack);

		return $item;

	}

	public static function dumpException( \Exception $e ) {

		$item = get_class($e);

		$meta = [
			'message'  => $e->getMessage(),
			'code'     => $e->getCode(),
			'file'     => $e->getFile(),
			'line'     => $e->getLine(),
			'trace'    => static::dumpTrace($e->getTrace()),
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
				$item .= static::dumpMeta(
					stream_get_meta_data($resource)
				);
				break;

			case 'curl':
				$item .= static::dumpMeta(
					curl_getinfo($resource)
				);
				break;

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

	protected static function dumpTrace( array $trace ) {

		$lines = [];

		foreach( $trace as $i => $frame ) {

			$line = '';

			if( isset($frame['class']) )
				$line .= $frame['class']. $frame['type'];

			$line .= $frame['function']. '()';

			if( isset($frame['file']) ) {
				$line .= ' ['. $frame['file'];
				if( isset($frame['line']) )
					$line .= ':'. $frame['line'];
				$line .= ']';
			}

			$lines[] = $line;

		}

		return $lines;

	}

	protected static function dumpObjectProperties( $obj ) {

		// we use reflection to access all the object's properties (public, protected and private)
		$r = new \ReflectionObject($obj);

		$item = '';

		foreach( static::getClassProperties($r) as $p ) {
			$p->setAccessible(true);
			$item .= sprintf("%s%s: %s\n", str_repeat("\t", static::$depth), $p->name, static::dump($p->getValue($obj), false));
		}

		return $item;

	}

	protected static function getClassProperties( \ReflectionClass $class ) {

		$properties = [];

	    foreach( $class->getProperties() as $property ) {
	        $properties[$property->getName()] = $property;
	    }

	    if( $parent = $class->getParentClass() ) {
	        $parent_props = static::getClassProperties($parent);
	        if(count($parent_props) > 0)
	            $properties = array_merge($parent_props, $properties);
	    }

	    return $properties;

	}

	protected static function recursionCheck( $obj ) {

		if( end(static::$stack) === $obj )
			return '**SELF**';

		elseif( in_array($obj, static::$stack) )
			return '**RECURSION**';

		return '';

	}

}

// EOF