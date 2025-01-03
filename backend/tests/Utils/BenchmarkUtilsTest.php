<?php

declare(strict_types=1);

namespace FinGather\Tests\Utils;

use FinGather\Utils\BenchmarkUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BenchmarkUtils::class)]
final class BenchmarkUtilsTest extends TestCase
{
	public function testBenchmark(): void
	{
		$callback = fn() => usleep(1000);

		$executionTime = BenchmarkUtils::benchmark($callback);

		self::assertGreaterThan(0, $executionTime);
	}
}
