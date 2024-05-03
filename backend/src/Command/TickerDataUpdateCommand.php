<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Service\Provider\TickerProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TickerDataUpdateCommand extends Command
{
	protected function configure(): void
	{
		$this->setName('tickerData:update');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('Ticker Data update was started.');

		$application = ApplicationFactory::create();

		$tickerDataProvider = $application->container->get(TickerDataProvider::class);
		assert($tickerDataProvider instanceof TickerDataProvider);

		$tickerProvider = $application->container->get(TickerProvider::class);
		assert($tickerProvider instanceof TickerProvider);

		$activeTickers = $tickerProvider->getActiveTickers();
		foreach ($activeTickers as $ticker) {
			$tickerDataProvider->updateTickerData($ticker);
		}

		$output->writeln('Updated "' . count($activeTickers) . '" Tickers.');

		return 0;
	}
}
