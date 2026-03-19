<?php

declare(strict_types=1);

namespace FinGather\App\ServiceProvider;

use FinGather\Service\Authentication\AuthenticationService;
use FinGather\Service\Authentication\AuthenticationServiceInterface;
use FinGather\Service\Authentication\GoogleAuthService;
use FinGather\Service\Authentication\GoogleAuthServiceInterface;
use FinGather\Service\Provider\UserProvider;
use GuzzleHttp\Client as GuzzleClient;
use League\Container\ServiceProvider\AbstractServiceProvider;

final class AuthenticationServiceProvider extends AbstractServiceProvider
{
	public function provides(string $id): bool
	{
		return in_array($id, [
			AuthenticationServiceInterface::class,
			GoogleAuthServiceInterface::class,
		], true);
	}

	public function register(): void
	{
		$container = $this->getContainer();

		$container->add(AuthenticationServiceInterface::class, AuthenticationService::class)
			->addArgument(UserProvider::class);

		$container->add(GoogleAuthServiceInterface::class, GoogleAuthService::class)
			->addArgument(GuzzleClient::class);
	}
}
