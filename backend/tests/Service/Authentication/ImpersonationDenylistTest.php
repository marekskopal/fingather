<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Authentication;

use FinGather\Service\Authentication\ImpersonationDenylist;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImpersonationDenylist::class)]
final class ImpersonationDenylistTest extends TestCase
{
	#[TestWith(['DELETE', '/api/current-user', true])]
	#[TestWith(['PUT', '/api/current-user', true])]
	#[TestWith(['PUT', '/api/current-user/locale', true])]
	#[TestWith(['POST', '/api/authentication/password-reset-request', true])]
	#[TestWith(['POST', '/api/authentication/password-reset', true])]
	#[TestWith(['POST', '/api/api-keys/12', true])]
	#[TestWith(['DELETE', '/api/api-key/3', true])]
	#[TestWith(['delete', '/api/current-user', true])]
	public function testBlockedRoutes(string $method, string $path, bool $expected): void
	{
		self::assertSame($expected, ImpersonationDenylist::isBlocked($method, $path));
	}

	#[TestWith(['GET', '/api/current-user'])]
	#[TestWith(['GET', '/api/portfolios'])]
	#[TestWith(['POST', '/api/transaction/1'])]
	#[TestWith(['DELETE', '/api/asset/5'])]
	public function testAllowedRoutes(string $method, string $path): void
	{
		self::assertFalse(ImpersonationDenylist::isBlocked($method, $path));
	}
}
