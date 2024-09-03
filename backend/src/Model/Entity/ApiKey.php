<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Model\Repository\ApiKeyRepository;

#[Entity(repository: ApiKeyRepository::class)]
class ApiKey extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		private User $user,
		#[RefersTo(target: Portfolio::class)]
		private Portfolio $portfolio,
		#[Column(type: 'enum(Trading212)', typecast: ApiKeyTypeEnum::class)]
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
