<?php

declare(strict_types=1);

namespace FinGather\Command;

use FinGather\App\ApplicationFactory;
use FinGather\Model\Repository\PriceAlertRepository;
use FinGather\Service\Email\EmailFactory;
use FinGather\Service\Email\MailerFactory;
use FinGather\Service\PriceAlertChecker;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PriceAlertCheckCommand extends AbstractCommand
{
	protected function configure(): void
	{
		$this->setName('priceAlert:check');
	}

	protected function process(InputInterface $input, OutputInterface $output): int
	{
		$application = ApplicationFactory::create();

		$priceAlertChecker = $application->container->get(PriceAlertChecker::class);
		assert($priceAlertChecker instanceof PriceAlertChecker);

		$priceAlertRepository = $application->container->get(PriceAlertRepository::class);
		assert($priceAlertRepository instanceof PriceAlertRepository);

		$logger = $application->container->get(LoggerInterface::class);
		assert($logger instanceof LoggerInterface);

		$mailerFactory = $application->container->get(MailerFactory::class);
		assert($mailerFactory instanceof MailerFactory);

		$emailFactory = $application->container->get(EmailFactory::class);
		assert($emailFactory instanceof EmailFactory);

		$mailer = $mailerFactory->create();

		$triggeredAlerts = $priceAlertChecker->checkAlerts();

		foreach ($triggeredAlerts as $triggeredAlert) {
			$alert = $triggeredAlert['alert'];
			$currentValue = $triggeredAlert['currentValue'];
			$user = $alert->user;

			if (!$user->isEmailNotificationsEnabled) {
				$this->writeln('Skipping user ' . $user->id . ' - email notifications disabled.', $output);
				continue;
			}

			if (!$user->isEmailVerified) {
				$this->writeln('Skipping user ' . $user->id . ' - email not verified.', $output);
				continue;
			}

			try {
				$email = $emailFactory->createPriceAlertEmail(user: $user, priceAlert: $alert, currentValue: $currentValue);
				$mailer->send($email);

				$priceAlertChecker->markTriggered($alert);
				$priceAlertRepository->persist($alert);

				$this->writeln('Sent price alert email to user ' . $user->id . ' for alert ' . $alert->id . '.', $output);
			} catch (\Throwable $e) {
				$logger->error('Error sending price alert email to user ' . $user->id . ': ' . $e->getMessage());
				$this->writeln('Error sending email to user ' . $user->id . ': ' . $e->getMessage(), $output);
			}
		}

		$this->writeln('Checked ' . count($triggeredAlerts) . ' triggered alert(s).', $output);

		return self::SUCCESS;
	}
}
