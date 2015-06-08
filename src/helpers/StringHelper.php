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

/**
 *
 * Inflector implementation from: http://subosito.com/inflector-in-symfony-2
 */
class StringHelper {

	protected static $plural = array(
		'/(quiz)$/i'               => "$1zes",
		'/^(ox)$/i'                => "$1en",
		'/([m|l])ouse$/i'          => "$1ice",
		'/(matr|vert|ind)ix|ex$/i' => "$1ices",
		'/(x|ch|ss|sh)$/i'         => "$1es",
		'/([^aeiouy]|qu)y$/i'      => "$1ies",
		'/(hive)$/i'               => "$1s",
		'/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
		'/(shea|lea|loa|thie)f$/i' => "$1ves",
		'/sis$/i'                  => "ses",
		'/([ti])um$/i'             => "$1a",
		'/(tomat|potat|ech|her|vet)o$/i'=> "$1oes",
		'/(bu)s$/i'                => "$1ses",
		'/(alias)$/i'              => "$1es",
		'/(octop)us$/i'            => "$1i",
		'/(ax|test)is$/i'          => "$1es",
		'/(us)$/i'                 => "$1es",
		'/s$/i'                    => "s",
		'/$/'                      => "s"
	);

	protected static $singular = array(
		'/(quiz)zes$/i'             => "$1",
		'/(matr)ices$/i'            => "$1ix",
		'/(vert|ind)ices$/i'        => "$1ex",
		'/^(ox)en$/i'               => "$1",
		'/(alias)es$/i'             => "$1",
		'/(octop|vir)i$/i'          => "$1us",
		'/(cris|ax|test)es$/i'      => "$1is",
		'/(shoe)s$/i'               => "$1",
		'/(o)es$/i'                 => "$1",
		'/(bus)es$/i'               => "$1",
		'/([m|l])ice$/i'            => "$1ouse",
		'/(x|ch|ss|sh)es$/i'        => "$1",
		'/(m)ovies$/i'              => "$1ovie",
		'/(s)eries$/i'              => "$1eries",
		'/([^aeiouy]|qu)ies$/i'     => "$1y",
		'/([lr])ves$/i'             => "$1f",
		'/(tive)s$/i'               => "$1",
		'/(hive)s$/i'               => "$1",
		'/(li|wi|kni)ves$/i'        => "$1fe",
		'/(shea|loa|lea|thie)ves$/i'=> "$1f",
		'/(^analy)ses$/i'           => "$1sis",
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'  => "$1$2sis",
		'/([ti])a$/i'               => "$1um",
		'/(n)ews$/i'                => "$1ews",
		'/(h|bl)ouses$/i'           => "$1ouse",
		'/(corpse)s$/i'             => "$1",
		'/(us)es$/i'                => "$1",
		'/s$/i'                     => ""
	);

	protected static $irregular = array(
		'move'   => 'moves',
		'foot'   => 'feet',
		'goose'  => 'geese',
		'sex'    => 'sexes',
		'child'  => 'children',
		'man'    => 'men',
		'tooth'  => 'teeth',
		'person' => 'people'
	);

	protected static $uncountable = array(
		'sheep',
		'fish',
		'deer',
		'series',
		'species',
		'money',
		'rice',
		'information',
		'equipment'
	);

	/**
	 * Helpers cannot be instantiated.
	 */
	private function __construct() {}

	/**
	 * Parse a URL string into an array of components.
	 * Similar to the native parse_url except that the returned array will contain all components
	 * and the query component is replaced with an options component containing a decoded array.
	 *
	 * @param  string|array  $url        either a string array or a partial list of url components
	 * @param  array         $defaults   an array of default values for components
	 * @return array|boolean   Returns false if the URL could not be parsed
	 */
	public static function parseURL( $url, $defaults = array() ) {

		$parts = is_string($url) ? \parse_url(urldecode($url)) : $url;

		$select = function( $k ) use ( $parts, $defaults ) {
			if( isset($parts[$k]) )
				return $parts[$k];
			elseif( isset($defaults[$k]) )
				return $defaults[$k];
			else
				return '';
		};

		$url = array(
			'scheme'  => $select('scheme'),
			'host'    => $select('host'),
			'port'    => $select('port'),
			'user'    => $select('user'),
			'pass'    => $select('pass'),
			'path'    => $select('path'),
			'options' => array(),
		);

		if( isset($parts['query']) )
			parse_str($parts['query'], $url['options']);

		return $url;

	}

	/**
	 * Returns a string of cryptographically strong random hex digits.
	 *
	 * @param  integer  $length   length of the desired hex string
	 * @return string
	 */
	public static function randomHex( $length = 40 ) {
		return bin2hex(openssl_random_pseudo_bytes($length / 2));
	}

	/**
	 * Returns a string of the specified length containing only the characters in the $allowed parameter.
	 * This function is not cryptographically strong.
	 *
	 * @param  string  $length    length of the desired string
	 * @param  string  $allowed   the characters allowed to appear in the output
	 * @return string
	 */
	public static function randomString( $length, $allowed = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ) {
		$out = '';
		$max = strlen($allowed) - 1;
		for ($i = 0; $i < $length; $i++) {
			$out .= $allowed[mt_rand(0, $max)];
		}
		return $out;
	}

	/**
	 * Convert a camel-cased string to lower case with underscores
	 */
	public static function uncamelise( $str ) {
		return mb_strtolower(
			preg_replace(
				'/^A-Z^a-z^0-9]+/',  '_',
				preg_replace('/([a-z\d])([A-Z])/u', '$1_$2',
					preg_replace('/([A-Z+])([A-Z][a-z])/u', '$1_$2', $str)
				)
			)
		);
	}

	/**
	 * Convert a string into a format safe for use in urls.
	 * Converts any accent characters to their equivalent normal characters
	 * and then any sequence of two or more non-alphanumeric characters to a dash.
	 *
	 * @param  string   $str   A string to convert to a slug
	 * @return string
	 */
	public static function slugify( $str ) {
		$chars = array('&' => '-and-', '€' => '-EUR-', '£' => '-GBP-', '$' => '-USD-');
		return trim(preg_replace('/([^a-z0-9]+)/u', '-', mb_strtolower(strtr(static::removeAccents($str), $chars))), '-');
	}

	/**
	 * Converts all accent characters to their ASCII counterparts.
	 *
	 * @param  string   $str   A string that might contain accent characters
	 * @return string
	 */
	public static function removeAccents( $str ) {
		$chars = array(
			'ª' => 'a', 'º' => 'o', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A',
			'Ä' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ă' => 'A', 'Ą' => 'A', 'à' => 'a',
			'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'ā' => 'a',
			'ă' => 'a', 'ą' => 'a', 'Ç' => 'C', 'Ć' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C',
			'Č' => 'C', 'ç' => 'c', 'ć' => 'c', 'ĉ' => 'c', 'ċ' => 'c', 'č' => 'c',
			'Đ' => 'D', 'Ď' => 'D', 'đ' => 'd', 'ď' => 'd', 'È' => 'E', 'É' => 'E',
			'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ę' => 'E',
			'Ě' => 'E', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e',
			'ĕ' => 'e', 'ė' => 'e', 'ę' => 'e', 'ě' => 'e', 'ƒ' => 'f', 'Ĝ' => 'G',
			'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G', 'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g',
			'ģ' => 'g', 'Ĥ' => 'H', 'Ħ' => 'H', 'ĥ' => 'h', 'ħ' => 'h', 'Ì' => 'I',
			'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ĩ' => 'I', 'Ī' => 'I', 'Ĭ' => 'I',
			'Į' => 'I', 'İ' => 'I', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			'ĩ' => 'i', 'ī' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'Ĵ' => 'J',
			'ĵ' => 'j', 'Ķ' => 'K', 'ķ' => 'k', 'ĸ' => 'k', 'Ĺ' => 'L', 'Ļ' => 'L',
			'Ľ' => 'L', 'Ŀ' => 'L', 'Ł' => 'L', 'ĺ' => 'l', 'ļ' => 'l', 'ľ' => 'l',
			'ŀ' => 'l', 'ł' => 'l', 'Ñ' => 'N', 'Ń' => 'N', 'Ņ' => 'N', 'Ň' => 'N',
			'Ŋ' => 'N', 'ñ' => 'n', 'ń' => 'n', 'ņ' => 'n', 'ň' => 'n', 'ŉ' => 'n',
			'ŋ' => 'n', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
			'Ø' => 'O', 'Ō' => 'O', 'Ŏ' => 'O', 'Ő' => 'O', 'ò' => 'o', 'ó' => 'o',
			'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ō' => 'o', 'ŏ' => 'o',
			'ő' => 'o', 'ð' => 'o', 'Ŕ' => 'R', 'Ŗ' => 'R', 'Ř' => 'R', 'ŕ' => 'r',
			'ŗ' => 'r', 'ř' => 'r', 'Ś' => 'S', 'Ŝ' => 'S', 'Ş' => 'S', 'Š' => 'S',
			'Ș' => 'S', 'ś' => 's', 'ŝ' => 's', 'ş' => 's', 'š' => 's', 'ș' => 's',
			'ſ' => 's', 'Ţ' => 'T', 'Ť' => 'T', 'Ŧ' => 'T', 'Ț' => 'T', 'ţ' => 't',
			'ť' => 't', 'ŧ' => 't', 'ț' => 't', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U',
			'Ü' => 'U', 'Ũ' => 'U', 'Ū' => 'U', 'Ŭ' => 'U', 'Ů' => 'U', 'Ű' => 'U',
			'Ų' => 'U', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ũ' => 'u',
			'ū' => 'u', 'ŭ' => 'u', 'ů' => 'u', 'ű' => 'u', 'ų' => 'u', 'Ŵ' => 'W',
			'ŵ' => 'w', 'Ý' => 'Y', 'Ÿ' => 'Y', 'Ŷ' => 'Y', 'ý' => 'y', 'ÿ' => 'y',
			'ŷ' => 'y', 'Ź' => 'Z', 'Ż' => 'Z', 'Ž' => 'Z', 'ź' => 'z', 'ż' => 'z',
			'ž' => 'z', 'Æ' => 'AE', 'æ' => 'ae', 'Ĳ' => 'IJ', 'ĳ' => 'ij',
			'Œ' => 'OE', 'œ' => 'oe', 'ß' => 'ss', 'þ' => 'th', 'Þ' => 'th',
		);
		return strtr($str, $chars);
	}

	/**
	 * Converts a UTF-8 string to Latin-1 with unsupported characters encoded as numeric entities.
	 * Example: I want to turn text like
	 * hello é β 水
	 * into
	 * hello é &#946; &#27700;
	 *
	 * @param  string   $str
	 * @return string   the converted string.
	 */
	public static function latin1( $str ) {
		return utf8_decode(
			mb_encode_numericentity(
				(string) $str,
				array(0x0100, 0xFFFF, 0, 0xFFFF),
				'UTF-8'
			)
		);
	}

	/**
	 * Converts a Latin-1 string to UTF-8 and decodes entities.
	 *
	 * @param  string   $str
	 * @return string   the converted string.
	 */
	public static function utf8( $str ) {
		return html_entity_decode(
			mb_convert_encoding(
				(string) $str,
				'UTF-8',
				'ISO-8859-1'
			),
			ENT_NOQUOTES,
			'UTF-8'
		);
	}

	/**
	 * Return the ordinal suffix (st, nd, rd, th) of a number.
	 * Taken from: http://stackoverflow.com/questions/3109978/php-display-number-with-ordinal-suffix
	 *
	 * @param  integer   $n
	 * @return string    the number cast as a string with the ordinal suffixed.
	 */
	public static function ordinal( $n ) {
		$ends = array('th','st','nd','rd','th','th','th','th','th','th');
		// if tens digit is 1, 2 or 3 then use th instead of usual ordinal
		if( ($n % 100) >= 11 && ($n % 100) <= 13 )
		   return "{$n}th";
		else
		   return "{$n}{$ends[$n % 10]}";
	}

	/**
	 * Convert a number of bytes to a human-friendly string using the largest suitable unit.
	 * Taken from: http://www.php.net/manual/de/function.filesize.php#91477
	 *
	 * @param  integer   $bytes       the number of bytes to
	 * @param  integer   $precision   the number of decimal places to format the result to.
	 * @return string
	 */
	public static function sizeFormat( $bytes, $precision ) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		$bytes = max($bytes, 0);
		$pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow   = min($pow, count($units) - 1);
		$bytes /= (1 << (10 * $pow));
		return round($bytes, $precision). ' '. $units[$pow];
	}

	/**
	 * Converts a string representation containing one or more of hours, minutes and seconds into a total number of seconds.
	 * e.g. seconds("3 hours 4 minutes 10 seconds"), seconds("5min"), seconds("4.5h")
	 *
	 * @param  string  $str   string to convert
	 * @return integer|float
	 */
	public static function seconds( $str ) {

		$hours   = 0;
		$minutes = 0;
		$seconds = 0;

		if( preg_match('/^\d+:\d+$/', $str) ) {
			list(, $minutes, $seconds) = explode(':', $str);
		}
		elseif( preg_match('/^\d+:\d+:\d+$/', $str) ) {
			list($hours, $minutes, $seconds) = explode(':', $str);
		}
		else {

			// convert invalid characters to spaces
			$str = preg_replace('/[^a-z0-9. ]+/iu', ' ', $str);

			// strip multiple spaces
			$str = preg_replace('/ {2,}/u', ' ', $str);

			// compress scales and units together so '2 hours' => '2hours'
			$str = preg_replace('/([0-9.]+) ([cdehimnorstu]+)/u', '$1$2', $str);

			foreach( explode(' ', $str) as $item ) {

				if( !preg_match('/^([0-9.]+)([cdehimnorstu]+)$/u', $item, $m) )
					return false;

				list(, $scale, $unit) = $m;

				$scale = ((float) $scale != (int) $scale) ? (float) $scale : (int) $scale;

				if( preg_match('/^h(r|our|ours)?$/u', $unit) && !$hours ) {
					$hours = $scale;
				}
				elseif( preg_match('/^m(in|ins|inute|inutes)?$/u', $unit) && !$minutes ) {
					$minutes = $scale;
				}
				elseif( preg_match('/^s(ec|ecs|econd|econds)?$/u', $unit) && !$seconds ) {
					$seconds = $scale;
				}
				else {
					return false;
				}

			}

		}

		return ($hours * 3600) + ($minutes * 60) + $seconds;

	}

	/**
	 * Remove XSS vulnerabilities from a string.
	 * Shamelessly ripped from Kohana v2 and then tweaked to remove control characters
	 * and replace the associated regex components with \s instead.
	 * Also added a couple of other tags to the really bad list.
	 * Handles most of the XSS vectors listed at http://ha.ckers.org/xss.html
	 * @param  string|array   str
	 * @return string|array
	 */
	public static function xssClean( $str, $charset = 'UTF-8' ) {

		if( !$str )
			return $str;

		if( is_array($str) ) {
			foreach( $str as &$item ) {
				$item = static::xssClean($item);
			}
			return $str;
		}

		// strip any raw control characters that might interfere with our cleaning
		$str = static::stripControlChars($str);

		// fix and decode entities (handles missing ; terminator)
		$str = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $str);
		$str = preg_replace('/(&#*\w+)\s+;/u', '$1;', $str);
		$str = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $str);
		$str = html_entity_decode($str, ENT_COMPAT, $charset);

		// strip any control characters that were sneakily encoded as entities
		$str = static::stripControlChars($str);

		// normalise line endings
		$str = static::normaliseLineEndings($str);

		// remove any attribute starting with "on" or xmlns
		$str = preg_replace('#(?:on[a-z]+|xmlns)\s*=\s*[\'"\s]?[^\'>"]*[\'"\s]?\s?#iu', '', $str);

		// remove javascript: and vbscript: protocols and -moz-binding CSS property
		$str = preg_replace('#([a-z]*)\s*=\s*([`\'"]*)\s*j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:#iu', '$1=$2nojavascript...', $str);
		$str = preg_replace('#([a-z]*)\s*=([\'"]*)\s*v\s*b\s*s\s*c\s*r\s*i\s*p\s*t\s*:#iu', '$1=$2novbscript...', $str);
		$str = preg_replace('#([a-z]*)\s*=([\'"]*)\s*-moz-binding\s*:#u', '$1=$2nomozbinding...', $str);

		// only works in IE: <span style="width: expression(alert('XSS!'));"></span>
		$str = preg_replace('#(<[^>]+?)style\s*=\s*[`\'"]*.*?expression\s*\([^>]*+>#isu', '$1>', $str);
		$str = preg_replace('#(<[^>]+?)style\s*=\s*[`\'"]*.*?behaviour\s*\([^>]*+>#isu', '$1>', $str);
		$str = preg_replace('#(<[^>]+?)style\s*=\s*[`\'"]*.*?s\s*c\s*r\s*i\s*p\s*t\s*:*[^>]*+>#isu', '$1>', $str);

		// remove namespaced elements (we do not need them)
		$str = preg_replace('#</*\w+:\w[^>]*+>#iu', '', $str);

		// remove data URIs
		$str = preg_replace("#data:[\w/]+;\w+,[\w\r\n+=/]*#iu", "data: not allowed", $str);

		// remove really unwanted tags
		do {
			$old = $str;
			$str = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|body|embed|frame(?:set)?|head|html|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#iu', '', $str);
		}
		while ($old !== $str);

		return $str;
	}

	/**
	 * Remove every control character except newline (10/x0A) carriage return (13/x0D), and horizontal tab (09/x09)
	 * @param  string|array   str
	 * @return string|array
	 */
	public static function stripControlChars( $str ) {

		if( is_array($str) ) {
			foreach( $str as &$item ) {
				$item = static::stripControlChars($item);
			}
			return $str;
		}

		do {
			// 00-08, 11, 12, 14-31, 127
			$str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/Su', '', $str, -1, $count);
		}
		while ($count);

		return $str;

	}

	/**
	 * Ensures that a string has consistent line-endings.
	 * All line-ending are converted to LF with maximum of two consecutive.
	 * @return string
	 */
	public static function normaliseLineEndings() {
		$str = str_replace("\r\n", "\n", $str);
		$str = str_replace("\r", "\n", $str);
		return preg_replace("/\n{2,}/", "\n\n", $str);
	}

	// TODO: cache results to speed up subsequent use?
	public static function pluralise( $string ) {

		// save some time in the case that singular and plural are the same
		if ( in_array( mb_strtolower( $string ), self::$uncountable ) )
			return $string;


		// check for irregular singular forms
		foreach ( self::$irregular as $pattern => $result )
		{
			$pattern = '/' . $pattern . '$/iu';

			if ( preg_match( $pattern, $string ) )
				return preg_replace( $pattern, $result, $string);
		}

		// check for matches using regular expressions
		foreach ( self::$plural as $pattern => $result )
		{
			if ( preg_match( $pattern, $string ) )
				return preg_replace( $pattern, $result, $string );
		}

		return $string;

	}

	// TODO: cache results to speed up subsequent use?
	public static function singularise( $string ) {

		// save some time in the case that singular and plural are the same
		if ( in_array(mb_strtolower($string), self::$uncountable) )
			return $string;

		// check for irregular plural forms
		foreach( self::$irregular as $result => $pattern ) {
			$pattern = '/' . $pattern . '$/iu';
			if( preg_match($pattern, $string) )
				return preg_replace($pattern, $result, $string);
		}

		// check for matches using regular expressions
		foreach( self::$singular as $pattern => $result ) {
			if( preg_match($pattern, $string) )
				return preg_replace($pattern, $result, $string);
		}

		return $string;

	}

}

// EOF