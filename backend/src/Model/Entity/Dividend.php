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
use FinGather\Model\Repository\DividendRepository;

#[Entity(repository: DividendRepository::class)]
final class Dividend
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[RefersTo(target: Asset::class)]
		public readonly Asset $asset,
		#[RefersTo(target: Broker::class)]
		public readonly Broker $broker,
		#[Column(type: 'timestamp')]
		public readonly \DateTime $paidDate,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $priceGross,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $priceNet,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $tax,
		#[RefersTo(target: Currency::class)]
		public readonly Currency $currency,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $exchangeRate,
	) {
	}
}
