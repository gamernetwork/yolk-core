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

class ArrayHelper {

	/**
	 * Helpers cannot be instantiated.
	 */
	private function __construct() {}

	/**
	 * Convert a variable into an array of unique integer values.
	 *
	 * @param  mixed   $var
	 * @return array
	 */
	public static function uniqueIntegers( $var ) {
		return array_unique(
			array_map(
				'intval',
				(array) $var
			)
		);
	}

	/**
	 * Determines if a variable can be iterated via foreach.
	 * @param  mixed  $var
	 * @return boolean
	 */
	public static function isTraversable( $var ) {
		return is_array($var) || ($var instanceof \Traversable);
	}

	/**
	 * Determine if an array is an associative array.
	 * Taken from: http://stackoverflow.com/questions/173400/php-arrays-a-good-way-to-check-if-an-array-is-associative-or-numeric/4254008#4254008
	 *
	 * @param  array   $var
	 * @return bool
	 */
	public static function isAssoc( $var ) {
		return (bool) count(
			array_filter(
				array_keys($var),
				'is_string'
			)
		);
	}

	/**
	 * Filter an array and return all entries that are instances of the specified class.
	 *
	 * @param  array   $var     An array to filter
	 * @param  string  $class   The class that items should be an instance of
	 * @return array
	 */
	public static function filterObjects( $var, $class ) {
		if( !is_array($var) )
			$var = array($var);
		return array_filter(
			$var,
			function( $item ) use ($class) {
				return ($item instanceof $class);
			}
		);
	}

	/**
	 * Return value of the specified key from an array or object or a default if the key isn't set.
	 * @param  array|object   $data
	 * @param  string|integer $key
	 * @param  mixed          $default
	 * @return mixed
	 */
	public static function get( $var, $key, $default = null ) {

		if( !$key )
			throw new \InvalidArgumentException('Missing value for $key argument');

		if( isset($var->$key) )
			return $var->$key;
		elseif( isset($var[$key]) )
			return $var[$key];
		else
			return $default;

	}

	/**
	 * Extract the items that are null from an array; keys are preserved.
	 * @param  array $var
	 * @return array
	 */
	public static function getNullItems( $var ) {
		return array_filter($var, function( $a ) { return $a === null; });
	}

	/**
	 * Extract a single field from an array of arrays or objects.
	 *
	 * @param  array   $vars            An array
	 * @param  string  $field           The field to get values from
	 * @param  boolean $preserve_keys   Whether or not to preserve the array keys
	 * @return array
	 */
	public static function pluck( $vars, $field, $perserve_keys = true ) {
		$values = [];
	    foreach( $vars as $k => $v ) {
	        if( is_object($v) && isset($v->{$field}) ) {
		    	$values[$k] = $v->{$field};
	        }
	        elseif( isset($v[$field]) ) {
	            $values[$k] = $v[$field];
	        }
	    }
	    return $perserve_keys ? $values : array_values($values);
	}

	/**
	 * Sum a single field from an array of arrays or objects.
	 *
	 * @param  array   $vars    An array
	 * @param  string  $field   The field to get values from
	 * @return array
	 */
	public static function sum( $vars, $field ) {
		return array_sum(static::pluck($vars, $field));
	}

	/**
	 * Return the minimum value of a single field from an array of arrays or objects.
	 *
	 * @param  array   $vars    An array
	 * @param  string  $field   The field to get values from
	 * @return array
	 */
	public static function min( $vars, $field ) {
		return min(static::pluck($vars, $field));
	}

	/**
	 * Return the maximum value of a single field from an array of arrays or objects.
	 *
	 * @param  array   $vars    An array
	 * @param  string  $field   The field to get values from
	 * @return array
	 */
	public static function max( $vars, $field ) {
		return max(static::pluck($vars, $field));
	}

	/**
	 * Implode an associative array into a string of key/value pairs.
	 *
	 * @param  array   $var          The array to implode
	 * @param  string  $glue_outer   A string used to delimit items
	 * @param  string  $glue_inner   A string used to separate keys and values 
	 * @param  boolean $skip_empty   Should empty values be included?
	 * @return string
	 */
	public static function implodeAssoc( $var, $glue_outer = ',', $glue_inner = '=', $skip_empty = true ) {
		$output = [];
		foreach( $var as $k => $v ) {
			if( !$skip_empty || !empty($v) ) {
				$output[] = "{$k}{$glue_inner}{$v}";
			}
		}
		return implode($glue_outer, $output);
	}

	/**
	 * Create a comparison function for sorting multi-dimensional arrays.
	 * http://stackoverflow.com/questions/96759/how-do-i-sort-a-multidimensional-array-in-php/16788610#16788610
	 *
	 * Each parameter to this function is a criteria and can either be a string
	 * representing a column to sort or a numerically indexed array containing:
	 * 0 => the column name to sort on (mandatory)
	 * 1 => either SORT_ASC or SORT_DESC (optional)
	 * 2 => a projection function (optional)
	 * 
	 * The return value is a function that can be passed to usort() or uasort().
	 *
	 * @return \Closure
	 */
	public static function makeComparer() {

		// normalize criteria up front so that the comparer finds everything tidy
		$criteria = func_get_args();
		foreach( $criteria as $index => $criterion ) {
			$criteria[$index] = is_array($criterion)
				? array_pad($criterion, 3, null)
				: array($criterion, SORT_ASC, null);
		}

		return function( $first, $second ) use ($criteria) {
			foreach( $criteria as $criterion ) {

				// how will we compare this round?
				list($column, $sort_order, $projection) = $criterion;
				$sort_order = $sort_order === SORT_DESC ? -1 : 1;

				// if a projection was defined project the values now
				if( $projection ) {
					$lhs = call_user_func($projection, $first[$column]);
					$rhs = call_user_func($projection, $second[$column]);
				}
				else {
					$lhs = $first[$column];
					$rhs = $second[$column];
				}

				// do the actual comparison; do not return if equal, move on to the next column
				if( $lhs < $rhs ) {
					return -1 * $sort_order;
				}
				else if ($lhs > $rhs) {
					return 1 * $sort_order;
				}

			}
			return 0; // all sortable columns contain the same values, so $first == $second
		};

	}

}

// EOF