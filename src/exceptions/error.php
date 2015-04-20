<?php

ini_set('display_errors', 'off');

// use default php error handler
set_error_handler(
	function( $severity, $message, $file, $line ) {
	  return false;
	}
);

$code = http_response_code();

// not an error code so make it one
if( $code < 400 ) {
	$code = 500;
	headers_sent() || header("HTTP/1.0 500 Internal Server Error");
}

?><!DOCTYPE html>
<html>

<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Internal Server Error</title>
	<link rel="icon" href="/favicon.ico" type="image/vnd.microsoft.icon"/>
	<style type="text/css">

		html * {
			margin:0;
			padding:0;
			box-sizing: border-box;
		}

		.container {
			width: 500px;
			min-width: 320px;
			max-width: 100%;
			margin: 0 auto;
		}

		.container > div {
			padding: 2rem;
			margin: 1rem;
			font-family: Helvetica;
			line-height: 1.5em;
			background: #f7f7f7;
			border: 1px solid #ddd;
			border-radius: 5px;
		}

		h1 {
			margin-bottom: 1.5rem;
		}

		p.code {
			margin-top: 1.5rem;
			color: #888;
			font-size: 0.85em;
		}

		@media (min-width: 600px) {
			.container {
				margin-top: 5rem;
			}
			.container > div {
				padding: 3rem;
			}
		}

	</style>
</head>

<body>

<div class="container">
	<div>
		<h1>Server Error</h1>
		<p>We're sorry! The server encountered an internal error and was unable to complete your request. Please try again later.</p>
		<p class="code">Error: <?=$code?></p>
	</div>
</div>

</body>

</html>