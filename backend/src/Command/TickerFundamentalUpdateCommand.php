<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\TickerFundamentalProviderInterface;
use FinGather\Service\Provider\TickerProviderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TickerFundamentalUpdateCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('tickerFundamental:update');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$tickerFundamentalProvider = $application->container->get(TickerFundamentalProviderInterface::class);
		assert($tickerFundamentalProvider instanceof TickerFundamentalProviderInterface);

		$tickerProvider = $application->container->get(TickerProviderInterface::class);
		assert($tickerProvider instanceof TickerProviderInterface);

		$activeTickers = iterator_to_array($tickerProvider->getActiveTickers(), false);
		foreach ($activeTickers as $ticker) {
			$tickerFundamental = $tickerFundamentalProvider->getTickerFundamental($ticker);
			if ($tickerFundamental === null) {
				$tickerFundamentalProvider->createTickerFundamental($ticker);
				continue;
			}

			$tickerFundamentalProvider->updateTickerFundamental($tickerFundamental);
		}

		$this->writeln('Updated "' . count($activeTickers) . '" Tickers.', $output);

		return self::SUCCESS;
	}
}
