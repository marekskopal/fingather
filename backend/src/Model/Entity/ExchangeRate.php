<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Entity\Enum\ActionTypeEnum;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\ExchangeRateRepository;

#[Entity(repository: ExchangeRateRepository::class)]
final class ExchangeRate
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[RefersTo(target: Currency::class)]
		public readonly Currency $currency,
		#[Column(type: 'timestamp')]
		public readonly \DateTime $date,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $rate,
	) {
	}
}
