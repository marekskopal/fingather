<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Update\SplitUpdater;
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

		$splitUpdater = $application->container->get(SplitUpdater::class);
		assert($splitUpdater instanceof SplitUpdater);

		$tickerProvider = $application->container->get(TickerProvider::class);
		assert($tickerProvider instanceof TickerProvider);

		$activeTickers = iterator_to_array($tickerProvider->getActiveTickers(), false);
		foreach ($activeTickers as $ticker) {
			$splitUpdater->updateSplits($ticker);
		}

		$this->writeln('Updated "' . count($activeTickers) . '" Tickers.', $output);

		return 0;
	}
}
