<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MigrationRunCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('migration:run');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$migrator = $application->dbContext->getMigrator();

		$migrator->migrate();

		$application->dbContext->clearCache();

		return self::SUCCESS;
	}
}
