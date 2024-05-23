<?php

declare(strict_types=1);

namespace FinGather\Tests\Utils;

use FinGather\Utils\StringUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringUtils::class)]
final class StringUtilsTest extends TestCase
{
	#[TestWith(['teSt', 'Test'])]
	#[TestWith(['test — test', 'Test - Test'])]
	public function testSanitizeName(string $name, string $expectedSanitizedName): void
	{
		self::assertSame($expectedSanitizedName, StringUtils::sanitizeName($name));
	}
}
