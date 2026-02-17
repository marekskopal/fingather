<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\DividendCalendarProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\UserProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DividendCalendarWarmupCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('dividendCalendar:warmup');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$userProvider = $application->container->get(UserProvider::class);
		assert($userProvider instanceof UserProvider);

		$portfolioProvider = $application->container->get(PortfolioProvider::class);
		assert($portfolioProvider instanceof PortfolioProvider);

		$dividendCalendarProvider = $application->container->get(DividendCalendarProvider::class);
		assert($dividendCalendarProvider instanceof DividendCalendarProvider);

		$logger = $application->container->get(LoggerInterface::class);
		assert($logger instanceof LoggerInterface);

		$maxApiCallsPerMinute = (int) getenv('TWELVEDATA_CALENDAR_MAX_API_CALLS_PER_MINUTE');
		$apiCallCount = 0;
		$minuteStart = time();

		foreach ($userProvider->getUsers() as $user) {
			foreach ($portfolioProvider->getPortfolios($user) as $portfolio) {
				try {
					$this->writeln('Warming up dividend calendar for user ' . $user->id . ', portfolio ' . $portfolio->id . '.', $output);

					$dividendCalendarProvider->getDividendCalendar(
						$user,
						$portfolio,
						function () use (&$apiCallCount, &$minuteStart, $maxApiCallsPerMinute, $output): void {
							$apiCallCount++;

							if ($apiCallCount < $maxApiCallsPerMinute) {
								return;
							}

							$elapsed = time() - $minuteStart;
							if ($elapsed < 60) {
								$sleepSeconds = 60 - $elapsed;
								$this->writeln('Rate limit reached, sleeping ' . $sleepSeconds . 's.', $output);
								sleep($sleepSeconds);
							}

							$apiCallCount = 0;
							$minuteStart = time();
						},
					);
				} catch (\Throwable $e) {
					$logger->error(
						'Error warming up dividend calendar for user ' . $user->id . ', portfolio ' . $portfolio->id . ': ' . $e->getMessage(),
					);
					$this->writeln(
						'Error for user ' . $user->id . ', portfolio ' . $portfolio->id . ': ' . $e->getMessage(),
						$output,
					);
				}
			}
		}

		return self::SUCCESS;
	}
}
