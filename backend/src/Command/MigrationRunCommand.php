<?php

namespace FinGather\Command;

use FinGather\Service\Dbal\DbContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationRunCommand extends Command
{
	protected function configure(): void
	{
		$this->setName('migration:run');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$dbContext = new DbContext(
			dsn: 'mysql:host=db;dbname=fingather',
			user: 'fingather',
			password: 'fingather',
		);

		$migrator = $dbContext->getMigrator();

		while($migrator->run() !== null) { }

		return 0;
	}
}