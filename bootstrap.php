<?php
$start_time   = microtime(true);
$start_memory = memory_get_usage();

use App\Framework\Core\Config\Config;
use App\Framework\Core\Config\IniConfigLoader;
use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Translate\IniTranslationLoader;
use App\Framework\Core\Translate\MessageFormatterFactory;
use App\Framework\Core\Translate\Translator;
use App\Framework\Middleware\EnvironmentMiddleware;
use App\Framework\Middleware\FinalRenderMiddleware;
use App\Framework\Middleware\LayoutDataMiddleware;
use App\Framework\Middleware\SessionMiddleware;
use App\Framework\TemplateEngine\MustacheAdapter;
use DI\ContainerBuilder;
use Phpfastcache\Helper\Psr16Adapter;
use Slim\App;
use Slim\Middleware\Session;
use SlimSession\Helper;
use Symfony\Component\Console\Application;

try
{
	/* @var App $app */
	require __DIR__ . '/vendor/autoload.php';
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
	$dotenv->load();

	$containerBuilder = new ContainerBuilder();
	$systemDir = realpath(__DIR__);
	$paths = [
		'systemDir' => $systemDir,
		'varDir' => $systemDir . '/var',
		'cacheDir' => $systemDir . '/var/cache',
		'logDir' => $systemDir . '/var/log',
		'translationDir' => $systemDir . '/translations',
		'configDir' => $systemDir . '/config',
		'migrationDir' => $systemDir . '/migrations',
		'commandDir' => $systemDir . '/src/Commands'
	];

	$containerBuilder->addDefinitions(['paths' => $paths]);
	$containerBuilder->addDefinitions($systemDir . '/config/services/_default.php'); // must be first
	$directoryIterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($systemDir . '/config/services', FilesystemIterator::SKIP_DOTS)
	);
	foreach ($directoryIterator as $file)
	{
		if (fnmatch('*.php', $file->getFilename())) {
			$containerBuilder->addDefinitions($file->getPathname());
		}
	}
	$container     = $containerBuilder->build();

	if (php_sapi_name() !== 'cli')
	{
		$middlewareLoader = require __DIR__ . '/config/middleware.php';
		$app = $middlewareLoader($container, $start_time, $start_memory);
	}
	else
	{
		$app              = $container->get(Application::class);
		$commandDirectory = $container->get('paths')['commandDir'];

		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($commandDirectory)) as $file)
		{
			if (!$file->isFile() || $file->getExtension() !== 'php')
				continue;

			$class = 'App\\Commands\\' . $file->getBasename('.php');
			if (class_exists($class))
				$app->add($container->get($class));

		}
	echo 'dd';
	}
}
catch (Exception $e)
{
	echo 'Exception: ' . $e->getMessage();
	exit();
}
return $app;