<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Update\TickerUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TickerUpdateCommand extends Command
{
	protected function configure(): void
	{
		$this->setName('ticker:update');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('Tickers update was started.');

		$application = ApplicationFactory::create();

		$tickerProvider = $application->container->get(TickerProvider::class);
		assert($tickerProvider instanceof TickerProvider);

		$tickerUpdater = $application->container->get(TickerUpdater::class);
		assert($tickerUpdater instanceof TickerUpdater);

		$tickerUpdater->updateTickers();

		$activeTickers = $tickerProvider->getActiveTickers();
		foreach ($activeTickers as $ticker) {
			$tickerUpdater->updateTicker($ticker);
		}

		$output->writeln('Tickers was updated.');

		return 0;
	}
}
