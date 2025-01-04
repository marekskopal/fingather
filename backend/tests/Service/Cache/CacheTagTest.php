<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Cache;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\CacheTag;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CacheTag::class)]
final class CacheTagTest extends TestCase
{
	/** @return list<array{0: string, 1: User|null, 2: Portfolio|null, 3: DateTimeImmutable|null, 4: list<string>}> */
	public static function getForSaveDataProvider(): array
	{
		return [
			['namespace', null, null, null, []],
			['namespace', UserFixture::getUser(), null, null, ['namespace-user-1']],
			['namespace', null, PortfolioFixture::getPortfolio(), null, ['namespace-portfolio-1']],
			['namespace', null, null, new DateTimeImmutable('2024-01-01'), ['namespace-date-1704067200']],
			['namespace', UserFixture::getUser(), PortfolioFixture::getPortfolio(), null, ['namespace-user-1', 'namespace-portfolio-1', 'namespace-user-1|portfolio-1']],
			[
				'namespace',
				UserFixture::getUser(),
				null,
				new DateTimeImmutable('2024-01-01'),
				['namespace-user-1', 'namespace-date-1704067200', 'namespace-user-1|date-1704067200'],
			],
			[
				'namespace',
				null,
				PortfolioFixture::getPortfolio(),
				new DateTimeImmutable('2024-01-01'),
				['namespace-portfolio-1', 'namespace-date-1704067200', 'namespace-portfolio-1|date-1704067200'],
			],
			[
				'namespace',
				UserFixture::getUser(),
				PortfolioFixture::getPortfolio(),
				new DateTimeImmutable('2024-01-01'),
				['namespace-user-1', 'namespace-portfolio-1', 'namespace-date-1704067200', 'namespace-user-1|portfolio-1|date-1704067200'],
			],
		];
	}

	/** @param list<string> $expected */
	#[DataProvider('getForSaveDataProvider')]
	public function testGetForSave(string $namespace, ?User $user, ?Portfolio $portfolio, ?DateTimeImmutable $date, array $expected): void
	{
		$cachedTags = CacheTag::getForSave($namespace, $user, $portfolio, $date);

		self::assertSame($expected, $cachedTags);
	}
}
