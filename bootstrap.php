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
		'translationsDir' => $systemDir . '/translations',
		'configDir' => $systemDir . '/config'
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
}
catch (Exception $e)
{
	echo 'Exception: ' . $e->getMessage();
	exit();
}
return $app;