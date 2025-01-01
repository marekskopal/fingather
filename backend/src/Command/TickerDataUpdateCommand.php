<?php

declare(strict_types=1);

namespace FinGather\Command;

use DateTimeImmutable;
use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Service\Provider\TickerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TickerDataUpdateCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('tickerData:update');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$tickerDataProvider = $application->container->get(TickerDataProvider::class);
		assert($tickerDataProvider instanceof TickerDataProvider);

		$tickerProvider = $application->container->get(TickerProvider::class);
		assert($tickerProvider instanceof TickerProvider);

		$dataProvider = $application->container->get(DataProvider::class);
		assert($dataProvider instanceof DataProvider);

		$firstDate = new DateTimeImmutable('today');

		$activeTickers = iterator_to_array($tickerProvider->getActiveTickers(), false);
		foreach ($activeTickers as $ticker) {
			$tickerFirstDate = $tickerDataProvider->updateTickerData($ticker);

			if ($tickerFirstDate !== null && $tickerFirstDate < $firstDate) {
				$firstDate = $tickerFirstDate;
			}
		}

		$dataProvider->deleteData(date: $firstDate);

		$this->writeln('Updated "' . count($activeTickers) . '" Tickers.', $output);

		return self::SUCCESS;
	}
}
