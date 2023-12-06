<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use FinGather\Model\Entity\Enum\ActionTypeEnum;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\TransactionRepository;

#[Entity(repository: TransactionRepository::class)]
final class Transaction
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[HasOne(target: User::class)]
		public readonly User $user,
		#[HasOne(target: Asset::class)]
		public readonly Asset $asset,
		#[HasOne(target: Broker::class)]
		public readonly Broker $broker,
		#[Column(type: 'enum(Undefined,Buy,Sell)')]
		public readonly ActionTypeEnum $actionType,
		#[Column(type: 'timestamp')]
		public readonly \DateTime $created,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $units,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $priceUnit,
		#[HasOne(target: Currency::class)]
		public readonly Currency $currency,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $exchangeRate,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $feeConversion,
		#[Column(type: 'tinyText')]
		public readonly ?string $notes,
		#[Column(type: 'string')]
		public readonly ?string $importIdentifier,
	) {
	}
}
