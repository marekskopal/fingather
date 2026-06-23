<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use FinGather\Controller\McpController;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Mcp\Server\FinGatherServer;
use FinGather\OAuth\AuthorizationServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use Psr\Container\ContainerInterface;
use ReflectionMethod;

#[CoversClass(McpController::class)]
final class McpControllerTest extends TestCase
{
	protected function tearDown(): void
	{
		putenv('PROXY_HOST');
	}

	/**
	 * Regression: the MCP transport's DNS-rebinding protection defaults to a localhost-only
	 * allowlist, which 403s ("Invalid Host header") the deployed public host. The controller
	 * must seed the allowlist from PROXY_HOST so production requests are accepted.
	 */
	public function testAllowedHostsIncludesProxyHost(): void
	{
		$hosts = $this->allowedHostsFor('www.fingather.com');

		self::assertContains('www.fingather.com', $hosts);
		self::assertContains('localhost', $hosts);
	}

	public function testAllowedHostsLowercasesProxyHost(): void
	{
		self::assertContains('www.fingather.com', $this->allowedHostsFor('WWW.FinGather.COM'));
	}

	public function testAllowedHostsWithoutProxyHostFallsBackToLocalhost(): void
	{
		putenv('PROXY_HOST');

		$hosts = $this->allowedHostsFor('');

		self::assertSame(['localhost', '127.0.0.1', '[::1]'], $hosts);
	}

	/** @return list<string> */
	private function allowedHostsFor(string $proxyHost): array
	{
		if ($proxyHost === '') {
			putenv('PROXY_HOST');
		} else {
			putenv('PROXY_HOST=' . $proxyHost);
		}

		$controller = new McpController(
			self::createStub(AuthorizationServiceInterface::class),
			self::createStub(McpUserContextInterface::class),
			new FinGatherServer(self::createStub(ContainerInterface::class)),
			self::createStub(ClientInterface::class),
		);

		/** @var list<string> $hosts */
		$hosts = (new ReflectionMethod($controller, 'allowedHosts'))->invoke($controller);

		return $hosts;
	}
}
