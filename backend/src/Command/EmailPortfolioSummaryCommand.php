<?php

declare(strict_types=1);

namespace FinGather\Command;

use DateTimeImmutable;
use FinGather\App\ApplicationFactory;
use FinGather\Service\Email\EmailFactory;
use FinGather\Service\Email\MailerFactory;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\UserProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class EmailPortfolioSummaryCommand extends AbstractCommand
{
	private const string ArgumentUserId = 'userId';

	protected function configure(): void
	{
		$this->setName('email:portfolioSummary');
		$this->addArgument(self::ArgumentUserId, InputArgument::OPTIONAL, 'User ID');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$userProvider = $application->container->get(UserProvider::class);
		assert($userProvider instanceof UserProvider);

		$portfolioProvider = $application->container->get(PortfolioProvider::class);
		assert($portfolioProvider instanceof PortfolioProvider);

		$portfolioDataProvider = $application->container->get(PortfolioDataProvider::class);
		assert($portfolioDataProvider instanceof PortfolioDataProvider);

		$logger = $application->container->get(LoggerInterface::class);
		assert($logger instanceof LoggerInterface);

		$mailerFactory = $application->container->get(MailerFactory::class);
		assert($mailerFactory instanceof MailerFactory);

		$emailFactory = $application->container->get(EmailFactory::class);
		assert($emailFactory instanceof EmailFactory);

		$mailer = $mailerFactory->create();

		$userId = $input->getArgument(self::ArgumentUserId);
		if (is_numeric($userId)) {
			$userEntity = $userProvider->getUser((int) $userId);
			if ($userEntity === null) {
				$this->writeln('User ' . $userId . ' not found.', $output);

				return self::FAILURE;
			}

			$users = [$userEntity];
		} else {
			$users = $userProvider->getUsers();
		}

		$dateTime = new DateTimeImmutable();

		foreach ($users as $user) {
			if (!$user->isEmailNotificationsEnabled) {
				$this->writeln('Skipping user ' . $user->id . ' - email notifications disabled.', $output);
				continue;
			}

			if (!$user->isEmailVerified) {
				$this->writeln('Skipping user ' . $user->id . ' - email not verified.', $output);
				continue;
			}

			try {
				$portfolio = $portfolioProvider->getDefaultPortfolio($user);
				$portfolioData = $portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);

				$email = $emailFactory->createPortfolioSummaryEmail(user: $user, portfolio: $portfolio, portfolioData: $portfolioData);

				$mailer->send($email);

				$this->writeln('Sent portfolio summary email to user ' . $user->id . '.', $output);
			} catch (\Throwable $e) {
				$logger->error('Error sending portfolio summary email to user ' . $user->id . ': ' . $e->getMessage());
				$this->writeln('Error sending email to user ' . $user->id . ': ' . $e->getMessage(), $output);
			}
		}

		return self::SUCCESS;
	}
}
