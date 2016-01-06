<?php

use yolk\Yolk;

defined('YOLK_START_TIME') || define('YOLK_START_TIME', microtime(true));
defined('YOLK_START_MEM')  || define('YOLK_START_MEM', memory_get_usage());

if( !function_exists('d') ) {
	function d() {
		$args = func_get_args();
		if( Yolk::isDebug() ) {
			foreach( $args as $arg ) {
				Yolk::dump($arg);
			}
		}
		return array_shift($args);
	}
	function dd() {
		if( Yolk::isDebug() ) {
			headers_sent() || header('Content-type: text/plain; charset=UTF-8');
			foreach( func_get_args() as $arg ) {
				Yolk::dump($arg);
			}
			die();
		}
	}
}

// EOF