<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Dividend;
use FinGather\Utils\DateTimeUtils;

final readonly class DividendDto
{
	public function __construct(
		public int $id,
		public int $assetId,
		public int $brokerId,
		public string $paidDate,
		public Decimal $priceGross,
		public Decimal $priceNet,
		public Decimal $tax,
		public int $currencyId,
		public Decimal $exchangeRate,
	) {
	}

	public static function fromEntity(Dividend $dividend): self
	{
		return new self(
			id: $dividend->getId(),
			assetId: $dividend->getAsset()->getId(),
			brokerId: $dividend->getBroker()->getId(),
			paidDate: DateTimeUtils::formatZulu($dividend->getPaidDate()),
			priceGross: new Decimal($dividend->getPriceGross()),
			priceNet: new Decimal($dividend->getPriceNet()),
			tax: new Decimal($dividend->getTax()),
			currencyId: $dividend->getCurrency()->getId(),
			exchangeRate: new Decimal($dividend->getExchangeRate()),
		);
	}
}
