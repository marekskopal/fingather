<?php

namespace FinGather\Command;

use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use FinGather\Service\Dbal\DbContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationGenerateCommand extends Command
{
	protected function configure(): void
	{
		$this->setName('migration:generate');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$dbContext = new DbContext(
			dsn: 'mysql:host=db;dbname=fingather',
			user: 'fingather',
			password: 'fingather',
		);

		$migrator = $dbContext->getMigrator();

		$generator = new GenerateMigrations($migrator->getRepository(), $migrator->getConfig());
		$generator->run($dbContext->getRegistry());

		return 0;
	}
}