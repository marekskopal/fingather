<?php

declare(strict_types=1);

namespace FinGather\Command;

use DateTimeImmutable;
use FinGather\App\ApplicationFactory;
use FinGather\Model\Repository\GoalRepository;
use FinGather\Service\Email\EmailFactory;
use FinGather\Service\Email\MailerFactory;
use FinGather\Service\Goal\GoalChecker;
use FinGather\Service\Provider\GoalProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GoalCheckCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('goal:check');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$goalProvider = $application->container->get(GoalProvider::class);
		assert($goalProvider instanceof GoalProvider);

		$goalChecker = $application->container->get(GoalChecker::class);
		assert($goalChecker instanceof GoalChecker);

		$goalRepository = $application->container->get(GoalRepository::class);
		assert($goalRepository instanceof GoalRepository);

		$logger = $application->container->get(LoggerInterface::class);
		assert($logger instanceof LoggerInterface);

		$mailerFactory = $application->container->get(MailerFactory::class);
		assert($mailerFactory instanceof MailerFactory);

		$emailFactory = $application->container->get(EmailFactory::class);
		assert($emailFactory instanceof EmailFactory);

		$mailer = $mailerFactory->create();

		$now = new DateTimeImmutable();
		$checkedCount = 0;

		foreach ($goalProvider->getActiveGoals() as $goal) {
			$checkedCount++;
			$currentValue = $goalChecker->getCurrentValue($goal, $now);

			if (!$goalChecker->isAchieved($goal, $currentValue)) {
				continue;
			}

			$user = $goal->user;

			if (!$user->isEmailNotificationsEnabled) {
				$this->writeln('Skipping user ' . $user->id . ' - email notifications disabled.', $output);
				$goalProvider->markAchieved($goal);
				continue;
			}

			if (!$user->isEmailVerified) {
				$this->writeln('Skipping user ' . $user->id . ' - email not verified.', $output);
				$goalProvider->markAchieved($goal);
				continue;
			}

			try {
				$email = $emailFactory->createGoalEmail(user: $user, goal: $goal, currentValue: $currentValue->toFixed(2));
				$mailer->send($email);

				$goalProvider->markAchieved($goal);

				$this->writeln('Sent goal achieved email to user ' . $user->id . ' for goal ' . $goal->id . '.', $output);
			} catch (\Throwable $e) {
				$logger->error('Error sending goal achieved email to user ' . $user->id . ': ' . $e->getMessage());
				$this->writeln('Error sending email to user ' . $user->id . ': ' . $e->getMessage(), $output);
			}
		}

		$this->writeln('Checked ' . $checkedCount . ' active goal(s).', $output);

		return self::SUCCESS;
	}
}
