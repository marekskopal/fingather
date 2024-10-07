<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Warmup\DatabaseWarmup;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class WarmupAllCommand extends AbstractBenchmarkCommand
{
	protected function configure(): void
	{
		$this->setName('warmup:all');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->writeln('Warmup was started.', $output);

		$application = ApplicationFactory::create();

		$databaseWarmup = $application->container->get(DatabaseWarmup::class);
		assert($databaseWarmup instanceof DatabaseWarmup);

		$benchmarkTime = $this->benchmark(fn() => $databaseWarmup->warmup());

		$this->writeln('Warmup was ended - ' . $benchmarkTime . 'ms', $output);

		return self::SUCCESS;
	}
}
