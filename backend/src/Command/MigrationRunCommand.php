<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use Psr\Log\LoggerInterface;
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

		$logger = $application->container->get(LoggerInterface::class);
		assert($logger instanceof LoggerInterface);

		try {
			$migrator = $application->dbContext->getMigrator();
			$migrator->migrate();
		} catch (\Throwable $e) {
			$output->writeln($e->getMessage());
			$logger->error($e);

			return self::FAILURE;
		}

		$application->dbContext->clearCache();

		return self::SUCCESS;
	}
}
