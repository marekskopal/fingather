<?php

declare(strict_types=1);

namespace FinGather\Tests\Utils;

use FinGather\Utils\BatchUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BatchUtils::class)]
final class BatchUtilsTest extends TestCase
{
	public function testCallbackNotInvokedForEmptyList(): void
	{
		$calls = 0;
		BatchUtils::batchCall([], 5, static function () use (&$calls): void {
			$calls++;
		});

		self::assertSame(0, $calls);
	}

	public function testItemsExactlyDivideIntoFullBatches(): void
	{
		$batches = [];
		BatchUtils::batchCall([1, 2, 3, 4], 2, static function (array $batch) use (&$batches): void {
			$batches[] = $batch;
		});

		self::assertSame([[1, 2], [3, 4]], $batches);
	}

	public function testFinalBatchShrinksToRemainingItems(): void
	{
		// 5 items / batch size 2 → batches of 2, 2, 1
		$batches = [];
		BatchUtils::batchCall([1, 2, 3, 4, 5], 2, static function (array $batch) use (&$batches): void {
			$batches[] = $batch;
		});

		self::assertSame([[1, 2], [3, 4], [5]], $batches);
	}

	public function testBatchSizeLargerThanInputProducesSingleBatch(): void
	{
		$batches = [];
		BatchUtils::batchCall([1, 2, 3], 10, static function (array $batch) use (&$batches): void {
			$batches[] = $batch;
		});

		self::assertSame([[1, 2, 3]], $batches);
	}
}
