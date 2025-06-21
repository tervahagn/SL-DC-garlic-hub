<?php
use App\Framework\Core\Config\Config;
use App\Framework\Core\Config\IniConfigLoader;
use App\Framework\Core\CsrfToken;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\App;
use Symfony\Component\Console\Application;

$start_time   = microtime(true);
$start_memory = memory_get_usage();

/* @var App $app */
$systemDir = realpath(__DIR__. '/../');

try
{
	if (!is_string($systemDir) || !file_exists($systemDir))
		throw new RuntimeException("Invalid or non-existent system directory provided: " . var_export($systemDir, true));

	require $systemDir.'/vendor/autoload.php';
	$dotenv = Dotenv\Dotenv::createImmutable($systemDir);
	$dotenv->load();
}
catch (Throwable $e)
{
	die('Error during initialization: ' . $e->getMessage());
}

if ($_ENV['APP_ENV'] === 'dev')
	require __DIR__ . '/bootstrap_dev.php';

$paths = [
	'systemDir' => $systemDir,
	'varDir' => $systemDir . '/var',
	'cacheDir' => $systemDir . '/var/cache',
	'logDir' => $systemDir . '/var/logs',
	'keysDir' => $systemDir . '/var/keys',
	'templateDir' => $systemDir . '/templates',
	'translationDir' => $systemDir . '/translations',
	'configDir' => $systemDir . '/config',
	'migrationDir' => $systemDir . '/migrations',
	'commandDir' => $systemDir . '/src/Commands'
];

$containerBuilder = new ContainerBuilder();
// The Config class has to load first
$containerBuilder->addDefinitions([
	Config::class => new Config(
		new IniConfigLoader($paths['configDir'].'/settings'),
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
try
{
	$container = $containerBuilder->build();

	$csrfToken = $container->get(CsrfToken::class);
	$csrfToken->generateToken();
}
catch (Exception $e)
{

}
if (php_sapi_name() !== 'cli')
{
	$middlewareLoader = require $systemDir.'/config/middleware.php';
	/** @var ContainerInterface $container  */
	$app = $middlewareLoader($container, $start_time, $start_memory);
}
else
{
	/** @var ContainerInterface $container  */
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

return $app;