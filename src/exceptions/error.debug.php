<?php

ini_set('display_errors', 'on');

// use default php error handler
set_error_handler(
	function( $severity, $message, $file, $line ) {
		return false;
	}
);

// map of error code to pretty strings
$names = [
	E_ERROR             => 'Error',
	E_PARSE             => 'Parse Error',
	E_CORE_ERROR        => 'Core Error',
	E_CORE_WARNING      => 'Core Warning',
	E_COMPILE_ERROR     => 'Compile Error',
	E_COMPILE_WARNING   => 'Compile Warning',
	E_WARNING           => 'Warning',
	E_NOTICE            => 'Notice',
	E_USER_ERROR        => 'User Error',
	E_USER_WARNING      => 'User Warning',
	E_USER_NOTICE       => 'User Notice',
	E_STRICT            => 'Strict Standards',
	E_RECOVERABLE_ERROR => 'Recoverable Error',
	E_DEPRECATED        => 'Deprecated',
	E_USER_DEPRECATED   => 'User Deprecated',
];

$err = [
	'name'    => '\\'. get_class($error),
	'code'    => $error->getCode(),
	'message' => $error->getMessage(),
	'file'    => $error->getFile(),
	'line'    => $error->getLine(),
	'trace'   => $error->getTrace(),
	'previous' => $error->getPrevious(),
];
if( $error instanceof \ErrorException ) {
	$err['name'] = $names[$error->getSeverity()];
}
elseif( ($error instanceof \InvalidArgumentException) && ($err['previous'] instanceof \ErrorException) ) {
	$err['file'] = $err['previous']->getFile();
	$err['line'] = $err['previous']->getLine();
}

// it's an error - don't send a 200 code!
if( http_response_code() == 200 )
	header("HTTP/1.0 500 Internal Server Error");

?><!DOCTYPE html>
<html>

<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<title><?=$err['name']?></title>
	<link rel="icon" href="/favicon.ico" type="image/vnd.microsoft.icon"/>
	<style type="text/css">

		html * {
			margin:0;
			padding:0;
		}

		body {
			font-family: Helvetica;
			color:#333333;
		}

		#header {
			background : #CD1818;
			color : #ffffff;
			padding:25px 20px 20px 20px;
		}

		#header p {
			font-size:20px;
		}

		h1 {
			margin-bottom:10px;
		}

		h2 {
			margin-bottom: 10px;
			color:#328ADC;
			font-size:18px;
		}

		#file, #trace {
			padding : 10px 20px 0px 20px;
			margin-bottom:20px;
		}

		#trace li p {
			margin-bottom:15px;
		}

		#file p,
		#trace li p:last-child {
			margin-bottom:3px;
		}

		.panel {
			background: #F1F5FB;
			padding:10px;
			border-radius:7px;
			-moz-border-radius:7px;
			-webkit-border-radius:7px;
		}

		ol {
		}

		li {
			margin: 0 0 10px 25px;
		}

		code {
			font-size:14px;
			border:1px solid #cccccc;
			padding:5px;
			border-radius:5px;
			-moz-border-radius:5px;
			-webkit-border-radius:5px;
		}

	</style>
</head>

<body>

<div id="header">
	<h1><?=$err['name']?></h1>
	<p><?=$err['message']?><?= $err['code'] ? " (Code {$err['code']})" : '' ?></p>
</div>

<div id="file">

	<h2>Source File:</h2>

	<div class="panel">
		<p>
			<strong>File:</strong>
			<code><?=$err['file']?></code>&nbsp;&nbsp;
			<strong>Line: </strong>
			<code><?=$err['line']?></code>
		</p>
	</div>

</div>

<?php if( $err['trace'] ) { ?>
<div id="trace">
	<h2>Trace:</h2>
	<ol>
		<?php foreach( $err['trace'] as $i => $item ) { ?>
		<li class="panel">
			<?php
				if( isset($item['file']) ) { ?>
			<p>
				<strong>File:</strong> <code><?=$item['file']; ?></code>
				<?php if( isset($item['line']) ) { ?> &nbsp;&nbsp;&nbsp;<strong>Line:</strong> <code><?=$item['line'] ?></code><?php } ?>
			</p>
			<?php } ?>
			<p><strong>Function: </strong><code><?=isset($item['class']) ? $item['class']. $item['type'] : ''?><?=$item['function']. '()'; ?></code></p>
		</li>
		<?php } ?>
	</ol>
</div>
<?php } ?>

</body>

</html>