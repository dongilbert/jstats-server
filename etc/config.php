<?php

return [
	'debug' => (getenv('ENV') === 'development'),
	'database' => [
		'host' => 'localhost',
		'user' => 'root',
		'password' => 'bitnami',
		'database' => 'jstats_server',
		'prefix' => 'jos_',
		'driver' => 'mysqli'
	],
];