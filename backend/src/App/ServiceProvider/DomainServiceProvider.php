<?php

declare(strict_types=1);

namespace FinGather\App\ServiceProvider;

use FinGather\Service\Goal\GoalChecker;
use FinGather\Service\Goal\GoalCheckerInterface;
use FinGather\Service\Provider\DcaPlanProvider;
use FinGather\Service\Provider\DcaPlanProviderInterface;
use FinGather\Service\Provider\GoalProvider;
use FinGather\Service\Provider\GoalProviderInterface;
use FinGather\Service\Request\RequestService;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Service\Task\TaskService;
use FinGather\Service\Task\TaskServiceInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

final class DomainServiceProvider extends AbstractServiceProvider
{
	public function provides(string $id): bool
	{
		return in_array($id, [
			RequestServiceInterface::class,
			TaskServiceInterface::class,
			DcaPlanProviderInterface::class,
			GoalProviderInterface::class,
			GoalCheckerInterface::class,
		], true);
	}

	public function register(): void
	{
		$container = $this->getContainer();

		$container->add(RequestServiceInterface::class, fn () => new RequestService());
		$container->add(TaskServiceInterface::class, fn () => new TaskService());

		$container->add(DcaPlanProviderInterface::class, DcaPlanProvider::class);

		$container->add(GoalProviderInterface::class, GoalProvider::class);

		$container->add(GoalCheckerInterface::class, GoalChecker::class);
	}
}
