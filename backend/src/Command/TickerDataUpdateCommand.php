<?php

declare(strict_types=1);

namespace FinGather\Command;

use DateTimeImmutable;
use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\DataProviderInterface;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Service\Provider\TickerProviderInterface;
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

		$tickerDataProvider = $application->container->get(TickerDataProviderInterface::class);
		assert($tickerDataProvider instanceof TickerDataProviderInterface);

		$tickerProvider = $application->container->get(TickerProviderInterface::class);
		assert($tickerProvider instanceof TickerProviderInterface);

		$dataProvider = $application->container->get(DataProviderInterface::class);
		assert($dataProvider instanceof DataProviderInterface);

		$firstDate = new DateTimeImmutable('today');

		$activeTickers = iterator_to_array($tickerProvider->getActiveTickers(), false);
		foreach ($activeTickers as $ticker) {
			$tickerFirstDate = $tickerDataProvider->updateTickerData($ticker);

			if ($tickerFirstDate !== null && $tickerFirstDate < $firstDate) {
				$firstDate = $tickerFirstDate;
			}
		}

		$dataProvider->deleteData(firstDate: $firstDate);

		$this->writeln('Updated "' . count($activeTickers) . '" Tickers.', $output);

		return self::SUCCESS;
	}
}
