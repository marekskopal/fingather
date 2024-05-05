<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;

final class TransactionFixture
{
	/** @api */
	public static function getTransaction(
		?User $user = null,
		?Portfolio $portfolio = null,
		?Asset $asset = null,
		?int $brokerId = null,
		?TransactionActionTypeEnum $actionType = null,
		?DateTimeImmutable $actionCreated = null,
		?TransactionCreateTypeEnum $createType = null,
		?DateTimeImmutable $created = null,
		?DateTimeImmutable $modified = null,
		?Decimal $units = null,
		?Decimal $price = null,
		?Currency $currency = null,
		?Decimal $priceTickerCurrency = null,
		?Decimal $priceDefaultCurrency = null,
		?Decimal $tax = null,
		?Currency $taxCurrency = null,
		?Decimal $taxTickerCurrency = null,
		?Decimal $taxDefaultCurrency = null,
		?Decimal $fee = null,
		?Currency $feeCurrency = null,
		?Decimal $feeTickerCurrency = null,
		?Decimal $feeDefaultCurrency = null,
		?string $notes = null,
		?string $importIdentifier = null,
	): Transaction {
		return new Transaction(
			user: $user ?? UserFixture::getUser(),
			portfolio: $portfolio ?? PortfolioFixture::getPortfolio(),
			asset: $asset ?? AssetFixture::getAsset(),
			brokerId: $brokerId ?? 1,
			actionType: $actionType ?? TransactionActionTypeEnum::Buy,
			actionCreated: $actionCreated ?? new \Safe\DateTimeImmutable('2021-01-01'),
			createType: $createType ?? TransactionCreateTypeEnum::Manual,
			created: $created ?? new \Safe\DateTimeImmutable('2021-01-01'),
			modified: $modified ?? new \Safe\DateTimeImmutable('2021-01-01'),
			units: $units ?? new Decimal(10),
			price: $price ?? new Decimal(100),
			currency: $currency ?? CurrencyFixture::getCurrency(),
			priceTickerCurrency: $priceTickerCurrency ?? new Decimal(100),
			priceDefaultCurrency: $priceDefaultCurrency ?? new Decimal(100),
			tax: $tax ?? new Decimal(2),
			taxCurrency: $taxCurrency ?? CurrencyFixture::getCurrency(),
			taxTickerCurrency: $taxTickerCurrency ?? new Decimal(2),
			taxDefaultCurrency: $taxDefaultCurrency ?? new Decimal(2),
			fee: $fee ?? new Decimal(1),
			feeCurrency: $feeCurrency ?? CurrencyFixture::getCurrency(),
			feeTickerCurrency: $feeTickerCurrency ?? new Decimal(1),
			feeDefaultCurrency: $feeDefaultCurrency ?? new Decimal(1),
			notes: $notes ?? 'Test Notes',
			importIdentifier: $importIdentifier ?? 'TestIdentifier',
		);
	}
}
