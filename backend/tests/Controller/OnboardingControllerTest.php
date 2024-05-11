<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use FinGather\Controller\OnboardingController;
use FinGather\Response\OkResponse;
use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Request\RequestServiceInterface;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OnboardingController::class)]
#[UsesClass(OkResponse::class)]
final class OnboardingControllerTest extends TestCase
{
	private OnboardingController $onboardingController;

	protected function setUp(): void
	{
		$this->onboardingController = new OnboardingController(
			$this->createMock(UserProvider::class),
			$this->createMock(RequestServiceInterface::class),
		);
	}

	public function testOnboardingCompleteReturnsOkResponseWhenValidRequest(): void
	{
		$request = new ServerRequest(method: 'POST', uri: '/api/onboarding-complete');

		$response = $this->onboardingController->actionPostOnboardingComplete($request);

		self::assertInstanceOf(OkResponse::class, $response);
	}
}
