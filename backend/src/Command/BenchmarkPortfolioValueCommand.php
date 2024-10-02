<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Repository\Enum\OrderDirectionEnum;
use FinGather\Model\Repository\Enum\TransactionOrderByEnum;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Provider\UserProvider;
use FinGather\Utils\DateTimeUtils;
use Safe\DateTimeImmutable;
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

		$transactionProvider = $application->container->get(TransactionProvider::class);
		assert($transactionProvider instanceof TransactionProvider);

		$userId = 2;
		$user = $userProvider->getUser($userId);
		if ($user === null) {
			throw new \Exception('User not found.');
		}

		$dataProvider->deleteData(user: $user);
		$portfolio = $portfolioProvider->getDefaultPortfolio($user);

		//BEGIN
		$timeStart = hrtime(true);

		$transactions = $transactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			actionTypes: [TransactionActionTypeEnum::Buy, TransactionActionTypeEnum::Sell],
			orderBy: [TransactionOrderByEnum::ActionCreated->value => OrderDirectionEnum::ASC],
		);

		if (count($transactions) === 0) {
			throw new \Exception('No transactions found.');
		}

		$firstTransaction = $transactions[array_key_first($transactions)];

		$range = RangeEnum::All;

		$datePeriod = DateTimeUtils::getDatePeriod(
			range: $range,
			firstDate: $firstTransaction->getActionCreated(),
			shiftStartDate: true,
		);
		foreach ($datePeriod as $dateTime) {
			/** @var \DateTimeImmutable $dateTime */
			$dateTimeConverted = DateTimeImmutable::createFromRegular($dateTime);

			$portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTimeConverted);
		}

		$timeEnd = hrtime(true);
		//END

		//@phpstan-ignore-next-line
		$benchmarkTime = ($timeEnd - $timeStart) / 1000000;

		$this->writeln('Benchmark was ended - ' . $benchmarkTime . 'ms', $output);

		return 0;
	}
}
