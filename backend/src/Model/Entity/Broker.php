<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Repository\BrokerRepository;

#[Entity(repository: BrokerRepository::class)]
class Broker extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		private User $user,
		#[RefersTo(target: Portfolio::class)]
		private Portfolio $portfolio,
		#[Column(type: 'string')]
		private string $name,
		#[Column(
			type: 'enum(Trading212,InteractiveBrokers,Xtb,Etoro,Revolut,Anycoin,Degiro,Portu,Coinbase,Binance)',
			typecast: BrokerImportTypeEnum::class,
		)]
		private BrokerImportTypeEnum $importType,
	) {
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getImportType(): BrokerImportTypeEnum
	{
		return $this->importType;
	}

	public function setImportType(BrokerImportTypeEnum $importType): void
	{
		$this->importType = $importType;
	}
}
