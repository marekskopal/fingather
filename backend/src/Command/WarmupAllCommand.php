<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Warmup\DatabaseWarmup;
use FinGather\Utils\BenchmarkUtils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class WarmupAllCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('warmup:all');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$databaseWarmup = $application->container->get(DatabaseWarmup::class);
		assert($databaseWarmup instanceof DatabaseWarmup);

		$benchmarkTime = BenchmarkUtils::benchmark(fn() => $databaseWarmup->warmup());

		$this->writeln('Warmup was finished - ' . $benchmarkTime . 'ms', $output);

		return self::SUCCESS;
	}
}
