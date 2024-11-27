<?php
$start_time   = microtime(true);
$start_memory = memory_get_usage();

use App\Framework\Core\Config\Config;
use App\Framework\Core\Config\IniConfigLoader;
use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Translate\IniTranslationLoader;
use App\Framework\Core\Translate\MessageFormatterFactory;
use App\Framework\Core\Translate\Translator;
use App\Framework\Middleware\FinalRenderMiddleware;
use App\Framework\Middleware\LayoutDataMiddleware;
use App\Framework\Middleware\SessionMiddleware;
use App\Framework\TemplateEngine\MustacheAdapter;
use DI\ContainerBuilder;
use Phpfastcache\Helper\Psr16Adapter;
use Slim\App;
use Slim\Middleware\Session;

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
		'configDir' => $systemDir . '/config'
	];

	$containerBuilder->addDefinitions(['paths' => $paths]);
	$containerBuilder->addDefinitions($systemDir . '/config/services/_default.php');
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
	$app           = $container->get(App::Class);

	// Middleware section
	// Be aware that the order of middleware registration matters.
	// Slim processes middleware in a Last In, First Out (LIFO) order during the Request phase.
	// This means $app->addRoutingMiddleware() is the first middleware to handle the Request,
	// and $app->addErrorMiddleware() is the last middleware to handle the Response.
	// Middleware added via $app->add() is processed in the order it is added.
	$errorMiddleware = $app->addErrorMiddleware($_ENV['APP_DEBUG'], true, true);

	// The code in these middlewares will execute AFTER the Controllers.
	// This happens because $handler->handle($request) is called first in their process() method.
	$app->add(new FinalRenderMiddleware(new MustacheAdapter($container->get(Mustache_Engine::class))));

	require_once __DIR__.'/config/route.php';

	// The code in these middlewares will execute BEFORE the Controllers.
	// This happens because $handler->handle($request) is called last in their process() method.
	$app->add(new LayoutDataMiddleware());

	$Config     = new Config(new IniConfigLoader(__DIR__.'/config/'));
	$Locales    = new Locales($Config, new \App\Framework\Core\Locales\UrlLocaleExtractor());
	$Translator = new Translator(
		$Locales,
		new IniTranslationLoader(__DIR__.'/translations/'),
		new MessageFormatterFactory(),
		new Psr16Adapter('Files')
	);
	$app->add(new \App\Framework\Middleware\EnvironmentMiddleware($Config, $Locales, $Translator));
	$app->add(new SessionMiddleware(new \SlimSession\Helper()));
	$app->add(new Session(['name' => 'garlic_session','autorefresh' => true, 'lifetime' => '1 hour']));
	$app->add(function ($request, $handler) use	($start_time, $start_memory)
	{
		$request = $request->withAttribute('start_time', $start_time);
		$request = $request->withAttribute('start_memory', $start_memory);
		return $handler->handle($request);
	});
	$app->addRoutingMiddleware();
}
catch (Exception $e)
{
	echo 'Exception: ' . $e->getMessage();
	exit();
}
return $app;