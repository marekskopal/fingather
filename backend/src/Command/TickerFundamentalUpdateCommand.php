<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\TickerFundamentalProvider;
use FinGather\Service\Provider\TickerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TickerFundamentalUpdateCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('tickerFundamental:update');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->writeln('Ticker Data update was started.', $output);

		$application = ApplicationFactory::create();

		$tickerFundamentalProvider = $application->container->get(TickerFundamentalProvider::class);
		assert($tickerFundamentalProvider instanceof TickerFundamentalProvider);

		$tickerProvider = $application->container->get(TickerProvider::class);
		assert($tickerProvider instanceof TickerProvider);

		$activeTickers = $tickerProvider->getActiveTickers();
		foreach ($activeTickers as $ticker) {
			$tickerFundamental = $tickerFundamentalProvider->getTickerFundamental($ticker);
			if ($tickerFundamental === null) {
				$tickerFundamentalProvider->createTickerFundamental($ticker);
				continue;
			}

			$tickerFundamentalProvider->updateTickerFundamental($tickerFundamental);
		}

		$this->writeln('Updated "' . count($activeTickers) . '" Tickers.', $output);

		return 0;
	}
}
