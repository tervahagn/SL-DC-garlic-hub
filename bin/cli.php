<?php

use App\Framework\Core\Cli\CliBase;
use App\Framework\Core\Cli\Dispatcher;
use App\Framework\Exceptions\BaseException;

require_once __DIR__.'/../bootstrap.php';

$system_dir   = __DIR__.'/../';
$input_dir    = $system_dir . 'src/Commands';
$meta_filepath  = $system_dir . 'var/command_metadata.json';
$should_update = in_array('--update', $argv) || !file_exists($meta_filepath);

try
{
	if ($should_update)
	{
		$extractor = new \App\Framework\Core\Cli\CommandMetadataExtractor(
			new \App\Framework\Core\Cli\Metadata\MetadataWriter($meta_filepath));
		$extractor->extractAndSave($input_dir);
	}

	$CliBase = new CliBase();
	$CliBase->parseBaseParams();

	$meta_file = json_decode(file_get_contents($meta_filepath), true);

	// Show help if --help-Parameter or no site-Parameter
	if ($CliBase->isHelp() === true || $CliBase->hasSiteParam() === false)
	{
		$CliBase->showCliHelp($meta_file);
		exit(0);
	}

	// execute the command
	$Dispatcher  = new Dispatcher();
	$Dispatcher->setCliBase($CliBase);
	$controller_file = $Dispatcher->dispatchApi($meta_file);
	require_once $controller_file;

}
catch (\Exception $e)
{
	if ($e instanceof BaseException)
		echo $e->getMessage();

	print PHP_EOL . $e->getMessage() . PHP_EOL;
	exit(255);
}
