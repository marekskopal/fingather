<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\DataProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\UserProviderInterface;
use FinGather\Service\Warmup\UserWarmup;
use FinGather\Utils\BenchmarkUtils;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class WarmupUserCommand extends AbstractCommand
{
	private const string ArgumentUserId = 'userId';

	private const string PortfolioId = 'portfolioId';

	private const string OptionDelete = 'delete';

	protected function configure(): void
	{
		$this->setName('warmup:user');
		$this->addArgument(self::ArgumentUserId, InputArgument::REQUIRED, 'User ID');
		$this->addArgument(self::PortfolioId, InputArgument::OPTIONAL, 'Portfolio ID');
		$this->addOption(self::OptionDelete, 'd', InputOption::VALUE_NONE, 'Delete all user data cache before warmup');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$userWarmup = $application->container->get(UserWarmup::class);
		assert($userWarmup instanceof UserWarmup);

		$userProvider = $application->container->get(UserProviderInterface::class);
		assert($userProvider instanceof UserProviderInterface);

		$portfolioProvider = $application->container->get(PortfolioProviderInterface::class);
		assert($portfolioProvider instanceof PortfolioProviderInterface);

		$dataProvider = $application->container->get(DataProviderInterface::class);
		assert($dataProvider instanceof DataProviderInterface);

		$userId = $input->getArgument(self::ArgumentUserId);
		if (!is_numeric($userId)) {
			$this->writeln('User ID must be a number.', $output);
			return self::FAILURE;
		}

		$user = $userProvider->getUser((int) $userId);
		if ($user === null) {
			$this->writeln('User not found.', $output);
			return self::FAILURE;
		}

		$delete = (bool) $input->getOption(self::OptionDelete);

		$portfolioId = $input->getArgument(self::PortfolioId);
		if (!is_numeric($portfolioId)) {
			if ($delete) {
				$this->writeln('Deleting all user data cache.', $output);
				$dataProvider->deleteUserData($user);
			}

			$benchmarkTime = BenchmarkUtils::benchmark(fn() => $userWarmup->warmup($user));

			$this->writeln('Warmup was finished - ' . $benchmarkTime . 'ms', $output);

			return self::SUCCESS;
		}

		$portfolio = $portfolioProvider->getPortfolio($user, (int) $portfolioId);
		if ($portfolio === null) {
			$this->writeln('Portfolio not found.', $output);
			return self::FAILURE;
		}

		if ($delete) {
			$this->writeln('Deleting all portfolio data cache.', $output);
			$dataProvider->deleteUserData($user, $portfolio);
		}

		$benchmarkTime = BenchmarkUtils::benchmark(fn() => $userWarmup->warmupPortfolio($user, $portfolio));

		$this->writeln('Warmup was finished - ' . $benchmarkTime . 'ms', $output);

		return self::SUCCESS;
	}
}
