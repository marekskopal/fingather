<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Repository\BrokerRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: BrokerRepository::class)]
class Broker extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		private User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		private Portfolio $portfolio,
		#[Column(type: 'string')]
		private string $name,
		#[ColumnEnum(enum: BrokerImportTypeEnum::class)]
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
