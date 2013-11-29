<?php

return [
	'debug' => (getenv('ENV') === 'development'),
	'database' => [
		'host' => '',
		'user' => '',
		'password' => '',
		'database' => '',
		'prefix' => '',
		'driver' => 'mysqli'
	],
];