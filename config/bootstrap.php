<?php

require dirname(__DIR__).'/vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/../.env');

if ((array_key_exists('APP_ENV', $_ENV) && $_ENV['APP_ENV'] === 'dev'))
{
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
}
else
{
	error_reporting(0);
	ini_set('display_errors', '0');
}

