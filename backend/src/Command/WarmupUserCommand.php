<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Warmup\UserWarmup;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class WarmupUserCommand extends AbstractCommand
{
	private const string ArgumentUserId = 'userId';

	protected function configure(): void
	{
		$this->setName('warmup:user');
		$this->addArgument(self::ArgumentUserId, InputArgument::REQUIRED, 'User ID');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$userWarmup = $application->container->get(UserWarmup::class);
		assert($userWarmup instanceof UserWarmup);

		$userProvider = $application->container->get(UserProvider::class);
		assert($userProvider instanceof UserProvider);

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

		$userWarmup->warmup($user);

		return self::SUCCESS;
	}
}
