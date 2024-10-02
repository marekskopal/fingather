<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Controller\PortfolioDataController;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Middleware\AuthorizationMiddleware;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\UserProvider;
use GuzzleHttp\Psr7\ServerRequest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Safe\hrtime;

final class BenchmarkPortfolioValueCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('benchmark:portfolioValue');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->writeln('Benchmark was started.', $output);

		$application = ApplicationFactory::create();

		$userProvider = $application->container->get(UserProvider::class);
		assert($userProvider instanceof UserProvider);

		$dataProvider = $application->container->get(DataProvider::class);
		assert($dataProvider instanceof DataProvider);

		$portfolioProvider = $application->container->get(PortfolioProvider::class);
		assert($portfolioProvider instanceof PortfolioProvider);

		$portfolioDataProvider = $application->container->get(PortfolioDataProvider::class);
		assert($portfolioDataProvider instanceof PortfolioDataProvider);

		$portfolioDataController = $application->container->get(PortfolioDataController::class);
		assert($portfolioDataController instanceof PortfolioDataController);

		$userId = 2;
		$user = $userProvider->getUser($userId);
		if ($user === null) {
			throw new \Exception('User not found.');
		}

		$dataProvider->deleteData(user: $user);
		$portfolio = $portfolioProvider->getDefaultPortfolio($user);

		$serverRequest = new ServerRequest('GET', '/api/portfolio-data-range/' . $portfolio->getId());
		$serverRequest = $serverRequest->withAttribute(AuthorizationMiddleware::AttributeUser, $user);
		$serverRequest = $serverRequest->withQueryParams(['range' => RangeEnum::All->value]);

		//BEGIN
		$timeStart = hrtime(true);

		$portfolioDataController->actionGetPortfolioDataRange($serverRequest, $portfolio->getId());

		$timeEnd = hrtime(true);
		//END

		//@phpstan-ignore-next-line
		$benchmarkTime = ($timeEnd - $timeStart) / 1000000;

		$this->writeln('Benchmark was ended - ' . $benchmarkTime . 'ms', $output);

		return 0;
	}
}
