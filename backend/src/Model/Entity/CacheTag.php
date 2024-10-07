<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\ForeignKey;
use DateTimeImmutable;
use DateTimeInterface;
use FinGather\Model\Repository\CacheTagRepository;
use FinGather\Service\Cache\CacheDriverEnum;

#[Entity(repository: CacheTagRepository::class)]
class CacheTag extends AEntity implements BulkQueryEntityInterface
{
	public function __construct(
		#[Column(type: 'string')]
		private string $key,
		#[Column(
			type: 'enum(memcached,redis)',
			default: CacheDriverEnum::Memcached->value,
			typecast: CacheDriverEnum::class,
		)]
		private CacheDriverEnum $driver,
		#[Column(type: 'integer', nullable: true)]
		#[ForeignKey(target: User::class)]
		private ?int $userId,
		#[Column(type: 'integer', nullable: true)]
		#[ForeignKey(target: Portfolio::class)]
		private ?int $portfolioId,
		#[Column(type: 'timestamp', nullable: true)]
		private ?DateTimeImmutable $date,
	) {
	}

	/** @return list<string> */
	public function getBulkInsertColumns(): array
	{
		return ['key', 'driver', 'user_id', 'portfolio_id', 'date'];
	}

	/** @return list<string|int|float|DateTimeInterface|null> */
	public function getBulkInsertValues(): array
	{
		return [$this->key, $this->driver->value, $this->userId, $this->portfolioId, $this->date];
	}
}
