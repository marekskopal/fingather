<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\LotMatcher;

use FinGather\Model\Entity\Enum\CostBasisMethodEnum;

final readonly class LotMatcherFactory
{
	public function __construct(private FifoLotMatcher $fifo, private LifoLotMatcher $lifo, private AverageCostLotMatcher $averageCost,)
	{
	}

	public function forMethod(CostBasisMethodEnum $method): LotMatcherInterface
	{
		return match ($method) {
			CostBasisMethodEnum::Fifo => $this->fifo,
			CostBasisMethodEnum::Lifo => $this->lifo,
			CostBasisMethodEnum::AverageCost => $this->averageCost,
		};
	}
}
