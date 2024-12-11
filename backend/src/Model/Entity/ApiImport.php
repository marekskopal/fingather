<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Entity\Enum\ApiImportStatusEnum;
use FinGather\Model\Repository\ApiImportRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: ApiImportRepository::class)]
class ApiImport extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		private User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		private Portfolio $portfolio,
		#[ManyToOne(entityClass: ApiKey::class)]
		private ApiKey $apiKey,
		#[ColumnEnum(enum: ApiImportStatusEnum::class)]
		private ApiImportStatusEnum $status,
		#[Column(type: 'timestamp')]
		protected DateTimeImmutable $dateFrom,
		#[Column(type: 'timestamp')]
		protected DateTimeImmutable $dateTo,
		#[Column(type: 'int', nullable: true)]
		private ?int $reportId,
		#[Column(type: 'string', nullable: true)]
		private ?string $error,
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

	public function getApiKey(): ApiKey
	{
		return $this->apiKey;
	}

	public function setStatus(ApiImportStatusEnum $status): void
	{
		$this->status = $status;
	}

	public function getDateTo(): DateTimeImmutable
	{
		return $this->dateTo;
	}

	public function getReportId(): ?int
	{
		return $this->reportId;
	}

	public function setError(?string $error): void
	{
		$this->error = $error;
	}
}
