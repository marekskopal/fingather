<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Update\TickerLogoUpdater;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TickerLogoUpdateCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('tickerLogo:update');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$tickerProvider = $application->container->get(TickerProvider::class);
		assert($tickerProvider instanceof TickerProvider);

		$tickerLogoUpdater = $application->container->get(TickerLogoUpdater::class);
		assert($tickerLogoUpdater instanceof TickerLogoUpdater);

		$activeTickers = iterator_to_array($tickerProvider->getActiveTickers(), false);
		foreach ($activeTickers as $ticker) {
			$tickerLogoUpdater->updateTickerLogo($ticker);
		}

		$this->writeln('Updated "' . count($activeTickers) . '" Tickers.', $output);

		return self::SUCCESS;
	}
}
