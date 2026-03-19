<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\UserProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class OnboardingController
{
	public function __construct(private UserProviderInterface $userProvider, private RequestServiceInterface $requestService)
	{
	}

	#[RoutePost(Routes::OnboardingComplete->value)]
	public function actionPostOnboardingComplete(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$this->userProvider->onboardingCompleteUser($user);

		return new OkResponse();
	}
}
