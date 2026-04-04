<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Service\Provider\TickerDcfValuationProviderInterface;
use FinGather\Service\Provider\TickerProviderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TickerDcfValuationUpdateCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('tickerDcfValuation:update');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$tickerDcfValuationProvider = $application->container->get(TickerDcfValuationProviderInterface::class);
		assert($tickerDcfValuationProvider instanceof TickerDcfValuationProviderInterface);

		$tickerProvider = $application->container->get(TickerProviderInterface::class);
		assert($tickerProvider instanceof TickerProviderInterface);

		$activeTickers = iterator_to_array($tickerProvider->getActiveTickers(), false);
		$count = 0;

		foreach ($activeTickers as $index => $ticker) {
			if ($ticker->type !== TickerTypeEnum::Stock) {
				continue;
			}

			$tickerDcfValuationProvider->createOrUpdateTickerDcfValuation($ticker);
			$count++;

			// Wait 1 minute between API calls to avoid rate limiting
			if ($index < count($activeTickers) - 1) {
				sleep(60);
			}
		}

		$this->writeln('Updated DCF valuation for "' . $count . '" Tickers.', $output);

		return self::SUCCESS;
	}
}
