<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\SplitProvider;
use FinGather\Service\Provider\TickerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SplitUpdateCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('split:update');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->writeln('Split update was started.', $output);

		$application = ApplicationFactory::create();

		$splitProvider = $application->container->get(SplitProvider::class);
		assert($splitProvider instanceof SplitProvider);

		$tickerProvider = $application->container->get(TickerProvider::class);
		assert($tickerProvider instanceof TickerProvider);

		$activeTickers = $tickerProvider->getActiveTickers();
		foreach ($activeTickers as $ticker) {
			$splitProvider->updateSplits($ticker);
		}

		$this->writeln('Updated "' . count($activeTickers) . '" Tickers.', $output);

		return 0;
	}
}
