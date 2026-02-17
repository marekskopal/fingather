<?php

declare(strict_types=1);

namespace FinGather\Command;

use DateTimeImmutable;
use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\ExchangeRateProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ExchangeRateUpdateCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('exchangeRate:update');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$exchangeRateProvider = $application->container->get(ExchangeRateProvider::class);
		assert($exchangeRateProvider instanceof ExchangeRateProvider);

		$currencyProvider = $application->container->get(CurrencyProvider::class);
		assert($currencyProvider instanceof CurrencyProvider);

		$dataProvider = $application->container->get(DataProvider::class);
		assert($dataProvider instanceof DataProvider);

		$firstDate = new DateTimeImmutable('today');

		foreach ($currencyProvider->getCurrencies() as $currency) {
			if ($currency->code === 'USD') {
				continue;
			}

			$exchangeRateFirstDate = $exchangeRateProvider->updateExchangeRates($currency);

			if ($exchangeRateFirstDate !== null && $exchangeRateFirstDate < $firstDate) {
				$firstDate = $exchangeRateFirstDate;
			}
		}

		$dataProvider->deleteData(firstDate: $firstDate);

		return self::SUCCESS;
	}
}
