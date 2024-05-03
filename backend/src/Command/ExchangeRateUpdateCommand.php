<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\ExchangeRateProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ExchangeRateUpdateCommand extends Command
{
	protected function configure(): void
	{
		$this->setName('exchangeRate:update');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('Exchange Rates update was started.');

		$application = ApplicationFactory::create();

		$exchangeRateProvider = $application->container->get(ExchangeRateProvider::class);
		assert($exchangeRateProvider instanceof ExchangeRateProvider);

		$currencyProvider = $application->container->get(CurrencyProvider::class);
		assert($currencyProvider instanceof CurrencyProvider);

		foreach ($currencyProvider->getCurrencies() as $currency) {
			if ($currency->getCode() === 'USD') {
				continue;
			}

			$exchangeRateProvider->updateExchangeRates($currency);
		}

		$output->writeln('Exchange Rates was updated.');

		return 0;
	}
}
