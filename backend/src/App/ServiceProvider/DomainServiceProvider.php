<?php

declare(strict_types=1);

namespace FinGather\App\ServiceProvider;

use FinGather\Model\Repository\DcaPlanRepository;
use FinGather\Model\Repository\GoalRepository;
use FinGather\Service\DataCalculator\DcaPlanDataCalculator;
use FinGather\Service\Goal\GoalChecker;
use FinGather\Service\Goal\GoalCheckerInterface;
use FinGather\Service\Payment\CheckoutService;
use FinGather\Service\Payment\CheckoutServiceInterface;
use FinGather\Service\Provider\DcaPlanProvider;
use FinGather\Service\Provider\DcaPlanProviderInterface;
use FinGather\Service\Provider\GoalProvider;
use FinGather\Service\Provider\GoalProviderInterface;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\SubscriptionProvider;
use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Request\RequestService;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Service\Task\TaskService;
use FinGather\Service\Task\TaskServiceInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Stripe\StripeClient;

final class DomainServiceProvider extends AbstractServiceProvider
{
	public function provides(string $id): bool
	{
		return in_array($id, [
			RequestServiceInterface::class,
			TaskServiceInterface::class,
			DcaPlanProviderInterface::class,
			GoalProviderInterface::class,
			CheckoutServiceInterface::class,
			GoalCheckerInterface::class,
		], true);
	}

	public function register(): void
	{
		$container = $this->getContainer();

		$container->add(RequestServiceInterface::class, fn () => new RequestService());
		$container->add(TaskServiceInterface::class, fn () => new TaskService());

		$container->add(DcaPlanProviderInterface::class, DcaPlanProvider::class)
			->addArguments([DcaPlanRepository::class, DcaPlanDataCalculator::class]);

		$container->add(GoalProviderInterface::class, GoalProvider::class)
			->addArgument(GoalRepository::class);

		$container->add(GoalCheckerInterface::class, GoalChecker::class)
			->addArgument(PortfolioDataProvider::class);
	}
}
