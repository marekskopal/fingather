<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Split;
use FinGather\Model\Entity\Ticker;
use FinGather\Service\Provider\Dto\SplitDto;

interface SplitProviderInterface
{
	/** @return list<SplitDto> */
	public function getSplits(Ticker $ticker): array;

	public function getSplit(Ticker $ticker, ?DateTimeImmutable $date = null): ?Split;

	public function createSplit(Ticker $ticker, DateTimeImmutable $date, Decimal $factor): Split;

	public function cleanCache(Ticker $ticker): void;
}
