<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Authentication;

use FinGather\Service\Authentication\ImpersonationContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImpersonationContext::class)]
final class ImpersonationContextTest extends TestCase
{
	public function testStartsInactive(): void
	{
		$context = new ImpersonationContext();

		self::assertFalse($context->isImpersonating());
		self::assertNull($context->getImpersonatorId());
		self::assertNull($context->getSessionId());
	}

	public function testActivateAndDeactivate(): void
	{
		$context = new ImpersonationContext();

		$context->activate(impersonatorId: 7, sessionId: 42);

		self::assertTrue($context->isImpersonating());
		self::assertSame(7, $context->getImpersonatorId());
		self::assertSame(42, $context->getSessionId());

		$context->deactivate();

		self::assertFalse($context->isImpersonating());
		self::assertNull($context->getImpersonatorId());
		self::assertNull($context->getSessionId());
	}
}
