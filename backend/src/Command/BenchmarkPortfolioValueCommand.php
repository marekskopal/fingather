<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Service\Authentication\AuthenticationService;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\UserProvider;
use FinGather\Utils\BenchmarkUtils;
use GuzzleHttp\Psr7\ServerRequest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class BenchmarkPortfolioValueCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('benchmark:portfolioValue');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$userProvider = $application->container->get(UserProvider::class);
		assert($userProvider instanceof UserProvider);

		$dataProvider = $application->container->get(DataProvider::class);
		assert($dataProvider instanceof DataProvider);

		$portfolioProvider = $application->container->get(PortfolioProvider::class);
		assert($portfolioProvider instanceof PortfolioProvider);

		$portfolioDataProvider = $application->container->get(PortfolioDataProvider::class);
		assert($portfolioDataProvider instanceof PortfolioDataProvider);

		$authenticationService = $application->container->get(AuthenticationService::class);
		assert($authenticationService instanceof AuthenticationService);

		$userId = 2;
		$user = $userProvider->getUser($userId);
		if ($user === null) {
			throw new \Exception('User not found.');
		}

		$dataProvider->deleteData(user: $user);
		$portfolio = $portfolioProvider->getDefaultPortfolio($user);

		$serverRequest = new ServerRequest('GET', '/api/portfolio-data-range/' . $portfolio->id);
		$serverRequest = $serverRequest->withQueryParams(['range' => RangeEnum::All->value]);
		$serverRequest = $authenticationService->addAuthenticationHeader($serverRequest, $user);

		$benchmarkTime = BenchmarkUtils::benchmark(fn() => $application->handler->handle($serverRequest));

		$this->writeln('Benchmark was finished - ' . $benchmarkTime . 'ms', $output);

		return self::SUCCESS;
	}
}
