<?php
$cli_meta = [
		'command' => 'migrate-database',
		'description' => 'Executes a database migration.',
		'usage' => 'cli.php -s migrate-database [-v] || cli.php --site update_feeds [--verbose]',
		'options' => [
			'-r, --revision' => 'Set revision number to migrate to'
		]
];

use App\Framework\Core\Cli\CliBase;
use App\Framework\Core\Cli\CliColors;
use App\Framework\Database\Migration\MigrateDatabase;
use DI\Container;

/**
 * @var $Config     		App\Framework\Core\Config
 * @var $CliBase            CliBase
 * @var $container          Container
 */

try
{
	$MigrateDatabase = new MigrateDatabase(
		$container->get(\App\Framework\Database\DBHandler::class),
		$container->get(\App\Framework\Database\QueryBuilder::class)
	);
	$MigrateDatabase->setSilentOutput(true);
	$Config = $container->get(\App\Framework\Core\Config::class);
	$path   = $Config->getConfigPath().'/../migrations/'.$_ENV['APP_PLATFORM_EDITION'].'/';
	$MigrateDatabase->setMigrationFilePath($path);
	$MigrateDatabase->execute();

	$msg = CliColors::colorizeString('Migration succeed', CliColors::CLI_COLOR_GREEN);
}
catch (Exception $e)
{
	$CliBase->showCliError('Migration failed: ' . $e->getMessage());
}
