<?php

use yolk\Yolk;

if( !function_exists('d') ) {
	function d() {
		if( Yolk::isDebug() ) {
			foreach( func_get_args() as $arg ) {
				Yolk::dump($arg);
			}
		}
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