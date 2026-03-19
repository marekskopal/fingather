<?php

declare(strict_types=1);

namespace FinGather\Command;

use DateTimeImmutable;
use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\CurrencyProviderInterface;
use FinGather\Service\Provider\DataProviderInterface;
use FinGather\Service\Provider\ExchangeRateProviderInterface;
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

		$exchangeRateProvider = $application->container->get(ExchangeRateProviderInterface::class);
		assert($exchangeRateProvider instanceof ExchangeRateProviderInterface);

		$currencyProvider = $application->container->get(CurrencyProviderInterface::class);
		assert($currencyProvider instanceof CurrencyProviderInterface);

		$dataProvider = $application->container->get(DataProviderInterface::class);
		assert($dataProvider instanceof DataProviderInterface);

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
