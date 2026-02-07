<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Update\TickerLogoUpdater;
use Psr\Log\LoggerInterface;
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

		$logger = $application->container->get(LoggerInterface::class);
		assert($logger instanceof LoggerInterface);

		$activeTickers = iterator_to_array($tickerProvider->getActiveTickers(), false);
		foreach ($activeTickers as $ticker) {
			try {
				$tickerLogoUpdater->updateTickerLogo($ticker);
			} catch (\Throwable $exception) {
				$logger->error('Error update ticker logo ' . $ticker->id . ': ' . $exception->getMessage());
				$this->writeln('Error update ticker logo for ticker ' . $ticker->id . ': ' . $exception->getMessage(), $output);
			}
		}

		$this->writeln('Updated "' . count($activeTickers) . '" Tickers.', $output);

		return self::SUCCESS;
	}
}
