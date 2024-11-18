<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use App\Framework\Core\Cli\CliBase;
use App\Framework\Core\Cli\Dispatcher;
use App\Framework\Exceptions\BaseException;

require_once __DIR__.'/../bootstrap.php';

$systemDir     = __DIR__.'/../';
$varDir		   = $systemDir . 'var';
$commandsDir   = $systemDir . 'src/Commands';
$metaFilepath  = 'command_metadata.json';
$shouldUpdate  = in_array('--update', $argv) || !file_exists($varDir.'/'.$metaFilepath);
$metaData      = [];
try
{
	if ($shouldUpdate)
	{
		$adapter    = new \League\Flysystem\Local\LocalFilesystemAdapter($varDir);
		$filesystem = new \League\Flysystem\Filesystem($adapter);
		$extractor  = new \App\Framework\Core\Cli\CommandMetadataExtractor();
		$writer     = new \App\Framework\Core\Cli\Metadata\MetadataWriter($filesystem, $metaFilepath);
		$metaData   = $extractor->extract($commandsDir);
		$writer->write($metaData);
		exit();
	}

	$CliBase = new CliBase();
	$CliBase->parseBaseParams();

	if (empty($metaData)) // because --update could ben at the same time
		$metaData = json_decode(file_get_contents($varDir.'/'.$metaFilepath), true);

	if (is_null($metaData))
		exit();

	// Show help if --help-Parameter or no site-Parameter
	if ($CliBase->isHelp() === true || $CliBase->hasSiteParam() === false)
	{
		$CliBase->showCliHelp($metaData);
		exit(0);
	}

	// execute the command
	$Dispatcher  = new Dispatcher();
	$Dispatcher->setCliBase($CliBase);
	$controller_file = $Dispatcher->dispatchApi($metaData);


	require_once $controller_file;

}
catch (\Exception $e)
{
	if ($e instanceof BaseException)
		echo $e->getMessage();

	print PHP_EOL . $e->getMessage() . PHP_EOL;
	exit(255);
}
