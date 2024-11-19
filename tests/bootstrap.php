<?php
require __DIR__ . '/../vendor/autoload.php';
$_ENV['APP_ENV'] = 'testing';

$systemDir = realpath(__DIR__);
$paths = [
	'systemDir' => $systemDir,
	'varDir' => $systemDir . '/var',
	'cacheDir' => $systemDir . '/var/cache',
	'logDir' => $systemDir . '/var/log',
	'configDir' => $systemDir . '/config'
];
