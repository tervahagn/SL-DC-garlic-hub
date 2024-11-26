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
 * @var $CliBase            CliBase
 * @var $container          Container
 */

try
{
    $MigrateDatabase = new MigrateDatabase($container->get('SqlConnection'));

    $MigrateDatabase->setSilentOutput(true);
    $path   = $container->get('paths')['systemDir'].'/migrations/'.$_ENV['APP_PLATFORM_EDITION'].'/';
    $MigrateDatabase->setMigrationFilePath($path);
    $MigrateDatabase->execute();

    $msg = CliColors::colorizeString('Migration succeed', CliColors::CLI_COLOR_GREEN);
}
catch (Exception $e)
{
    $CliBase->showCliError('Migration failed: ' . $e->getMessage());
}
catch (\Psr\Container\ContainerExceptionInterface $e)
{
    echo 'Error: '.$e->getMessage();
}
