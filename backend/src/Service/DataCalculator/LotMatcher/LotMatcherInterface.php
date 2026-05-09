<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\LotMatcher;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\FifoMatchDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Service\Provider\Dto\SplitDto;

interface LotMatcherInterface
{
	/**
	 * Consumes buy lots for a sell transaction, adjusting the $buys array in place.
	 * Returns the matched lot portions with their used units.
	 *
	 * @param array<int, TransactionBuyDto> $buys Modified in place: consumed lots are removed, partial lots are adjusted
	 * @param list<SplitDto> $splits
	 * @return list<FifoMatchDto>
	 */
	public function consumeLots(
		array &$buys,
		?int $brokerId,
		DateTimeImmutable $sellDate,
		Decimal $sellUnitsAbs,
		array $splits,
	): array;
}
