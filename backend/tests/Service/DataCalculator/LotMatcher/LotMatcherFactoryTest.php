<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator\LotMatcher;

use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Service\DataCalculator\LotMatcher\AverageCostLotMatcher;
use FinGather\Service\DataCalculator\LotMatcher\FifoLotMatcher;
use FinGather\Service\DataCalculator\LotMatcher\LifoLotMatcher;
use FinGather\Service\DataCalculator\LotMatcher\LotMatcherFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LotMatcherFactory::class)]
final class LotMatcherFactoryTest extends TestCase
{
	public function testReturnsFifoForFifoMethod(): void
	{
		$factory = $this->factory();
		self::assertInstanceOf(FifoLotMatcher::class, $factory->forMethod(CostBasisMethodEnum::Fifo));
	}

	public function testReturnsLifoForLifoMethod(): void
	{
		$factory = $this->factory();
		self::assertInstanceOf(LifoLotMatcher::class, $factory->forMethod(CostBasisMethodEnum::Lifo));
	}

	public function testReturnsAverageCostForAverageCostMethod(): void
	{
		$factory = $this->factory();
		self::assertInstanceOf(AverageCostLotMatcher::class, $factory->forMethod(CostBasisMethodEnum::AverageCost));
	}

	private function factory(): LotMatcherFactory
	{
		return new LotMatcherFactory(
			new FifoLotMatcher(),
			new LifoLotMatcher(),
			new AverageCostLotMatcher(),
		);
	}
}
