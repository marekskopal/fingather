<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Model\Repository\ApiKeyRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: ApiKeyRepository::class)]
class ApiKey extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		private User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		private Portfolio $portfolio,
		#[ColumnEnum(enum: ApiKeyTypeEnum::class)]
		private ApiKeyTypeEnum $type,
		#[Column(type: 'string')]
		private string $apiKey,
	) {
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function getPortfolio(): Portfolio
	{
		return $this->portfolio;
	}

	public function getType(): ApiKeyTypeEnum
	{
		return $this->type;
	}

	public function getApiKey(): string
	{
		return $this->apiKey;
	}

	public function setApiKey(string $apiKey): void
	{
		$this->apiKey = $apiKey;
	}
}
