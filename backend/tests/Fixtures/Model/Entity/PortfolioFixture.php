<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

final class PortfolioFixture
{
	public static function getPortfolio(
		?int $id = null,
		?User $user = null,
		?Currency $currency = null,
		?string $name = null,
		?bool $isDefault = null,
	): Portfolio
	{
		$portfolio = new Portfolio(
			user: $user ?? UserFixture::getUser(),
			currency: $currency ?? CurrencyFixture::getCurrency(),
			name: $name ?? 'Test Portfolio',
			isDefault: $isDefault ?? true,
		);

		$portfolio->id = $id ?? 1;

		return $portfolio;
	}
}
