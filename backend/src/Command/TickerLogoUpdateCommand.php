<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\TickerProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TickerLogoUpdateCommand extends Command
{
	protected function configure(): void
	{
		$this->setName('tickerLogo:update');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('Ticker Logo update was started.');

		$application = ApplicationFactory::create();

		$tickerProvider = $application->container->get(TickerProvider::class);
		assert($tickerProvider instanceof TickerProvider);

		$activeTickers = $tickerProvider->getActiveTickers();
		foreach ($activeTickers as $ticker) {
			$tickerProvider->updateTickerLogo($ticker);
		}

		$output->writeln('Updated "' . count($activeTickers) . '" Tickers.');

		return 0;
	}
}
