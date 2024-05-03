<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MigrationRunCommand extends Command
{
	protected function configure(): void
	{
		$this->setName('migration:run');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$migrator = $application->dbContext->getMigrator();

		while ($migrator->run() !== null) { //phpcs:ignore
		}

		return 0;
	}
}
