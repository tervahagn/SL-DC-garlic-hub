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

namespace App\Commands;

use App\Framework\Migration\MigrateDatabase;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
	name: 'app:migrate-database',
	description: 'Executes a database migration.'
)]
class MigrateCommand extends Command
{
	private MigrateDatabase $DatabaseMigration;
	private $paths;

	public function __construct(MigrateDatabase $DatabaseMigration, array $paths)
	{
		$this->DatabaseMigration = $DatabaseMigration;
		$this->paths = $paths;

		parent::__construct();
	}

	protected function configure(): void
	{
		$this->addArgument('version', InputArgument::OPTIONAL, 'Ziel-Migrationsversion');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$version = $input->getArgument('version');
		try
		{
			$path   = $this->paths['migrationDir'].'/'.$_ENV['APP_PLATFORM_EDITION'].'/';

			$this->DatabaseMigration->setSilentOutput(true);
			$this->DatabaseMigration->setMigrationFilePath($path);
			$this->DatabaseMigration->execute();

			$output->writeln('<info>Migration succeed.</info>');
		}
		catch (\Exception $e)
		{
			$output->writeln('<error>Migration failed: ' . $e->getMessage() . '</error>');
			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}
}
