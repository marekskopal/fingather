<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Warmup\DatabaseWarmup;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class WarmupAsyncCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('warmup:async');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->writeln('Warmup async was started.', $output);

		$application = ApplicationFactory::create();

		$databaseWarmup = $application->container->get(DatabaseWarmup::class);
		assert($databaseWarmup instanceof DatabaseWarmup);

		$databaseWarmup->warmupAsync();

		$this->writeln('Warmup async was ended.', $output);

		return self::SUCCESS;
	}
}
