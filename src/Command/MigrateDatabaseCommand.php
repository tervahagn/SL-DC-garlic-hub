<?php

namespace App\Command;

use App\Framework\Database\Migration\MigrateDatabase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
	name: 'app:migrate-database',
	description: 'Executes a database migration.'
)]
class MigrateDatabaseCommand extends Command
{
	private MigrateDatabase $DatabaseMigration;
	private KernelInterface $kernel;
	private ParameterBagInterface $params;

	public function __construct(MigrateDatabase $DatabaseMigration, $kernel, ParameterBagInterface $params)
	{
	    $this->DatabaseMigration = $DatabaseMigration;
	    $this->kernel = $kernel;
		$this->params = $params;
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
			$this->DatabaseMigration->setSilentOutput(true);
		    $path =	$this->kernel->getProjectDir().'/migrations/'.$this->params->get('platform_edition').'/';
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
