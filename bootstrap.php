<?php
$start_time   = microtime(true);
$start_memory = memory_get_usage();

use App\Framework\Core\Config\Config;
use App\Framework\Core\Config\IniConfigLoader;
use DI\ContainerBuilder;
use Slim\App;
use Symfony\Component\Console\Application;

try
{
	/* @var App $app */
	require __DIR__ . '/vendor/autoload.php';
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
	$dotenv->load();
	if ($_ENV['APP_ENV'] === 'dev')
		require __DIR__ . '/bootstrap_dev.php';

	$systemDir = realpath(__DIR__);
	$paths = [
		'systemDir' => $systemDir,
		'varDir' => $systemDir . '/var',
		'cacheDir' => $systemDir . '/var/cache',
		'logDir' => $systemDir . '/var/logs',
		'keysDir' => $systemDir . '/var/keys',
		'translationDir' => $systemDir . '/translations',
		'configDir' => $systemDir . '/config',
		'migrationDir' => $systemDir . '/migrations',
		'commandDir' => $systemDir . '/src/Commands'
	];

	$containerBuilder = new ContainerBuilder();
	// The Config class has to load first
	$containerBuilder->addDefinitions([
		Config::class => new Config(
			new IniConfigLoader($paths['configDir']),
			$paths,
			$_ENV
		),
	]);

	$containerBuilder->addDefinitions($systemDir . '/config/services/_default.php'); // must be the first file
	$directoryIterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($systemDir . '/config/services', FilesystemIterator::SKIP_DOTS)
	);
	foreach ($directoryIterator as $file)
	{
		if (fnmatch('*.php', $file->getFilename()))
		{
			$containerBuilder->addDefinitions($file->getPathname());
		}
	}
	$container     = $containerBuilder->build();
	if ($container->get(Config::class)->getEnv('APP_DEBUG'))
	{
		ini_set('display_errors', '0');
		ini_set('log_errors', '1');
		error_reporting(E_ALL);
	}

	if (php_sapi_name() !== 'cli')
	{
		$middlewareLoader = require __DIR__ . '/config/middleware.php';
		$app = $middlewareLoader($container, $start_time, $start_memory);
	}
	else
	{
		$app              = $container->get(Application::class);
		$config           = $container->get(Config::class);
		$commandDirectory = $config->getPaths('commandDir');

		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($commandDirectory)) as $file)
		{
			if (!$file->isFile() || $file->getExtension() !== 'php')
				continue;

			$class = 'App\\Commands\\' . $file->getBasename('.php');
			if (class_exists($class))
				$app->add($container->get($class));

		}
	}
}
catch (Exception $e)
{
	echo 'Exception: ' . $e->getMessage();
	exit();
}
return $app;