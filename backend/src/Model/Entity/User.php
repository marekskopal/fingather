<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\UserRepository;

#[Entity(repository: UserRepository::class)]
final class User
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[Column(type: 'string')]
		public readonly int $email,
		#[Column(type: 'string')]
		public readonly string $password,
		#[Column(type: 'string')]
		public readonly string $name,
		#[RefersTo(target: Currency::class, innerKey:'default_currency_id')]
		public readonly Currency $defaultCurrency,
	) {
	}
}
