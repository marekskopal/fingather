<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\PriceAlertNotificationDto;
use FinGather\Jobs\Message\ReceivedMessageInterface;
use FinGather\Service\Email\EmailFactory;
use FinGather\Service\Email\MailerFactory;
use FinGather\Service\Provider\PriceAlertProviderInterface;
use FinGather\Service\Provider\UserProviderInterface;
use FinGather\Service\Task\TaskServiceInterface;
use Psr\Log\LoggerInterface;

final readonly class PriceAlertNotificationHandler implements JobHandler
{
	public function __construct(
		private LoggerInterface $logger,
		private TaskServiceInterface $taskService,
		private UserProviderInterface $userProvider,
		private PriceAlertProviderInterface $priceAlertProvider,
		private MailerFactory $mailerFactory,
		private EmailFactory $emailFactory,
	) {
	}

	public function handle(ReceivedMessageInterface $message): void
	{
		$payload = $this->taskService->getPayloadDto($message, PriceAlertNotificationDto::class);

		$user = $this->userProvider->getUser($payload->userId);
		if ($user === null) {
			$this->logger->warning('Price alert notification: user ' . $payload->userId . ' not found.');
			return;
		}

		$priceAlert = $this->priceAlertProvider->getPriceAlert($payload->priceAlertId, $user);
		if ($priceAlert === null) {
			$this->logger->warning('Price alert notification: alert ' . $payload->priceAlertId . ' not found.');
			return;
		}

		if (!$user->isEmailNotificationsEnabled || !$user->isEmailVerified) {
			$this->logger->info('Skipping price alert email for user ' . $user->id . ' - notifications disabled or email not verified.');
			return;
		}

		try {
			$email = $this->emailFactory->createPriceAlertEmail(user: $user, priceAlert: $priceAlert, currentValue: $payload->currentValue);
			$this->mailerFactory->create()->send($email);
			$this->logger->info('Sent price alert email to user ' . $user->id . ' for alert ' . $priceAlert->id . '.');
		} catch (\Throwable $e) {
			$this->logger->error('Error sending price alert email to user ' . $user->id . ': ' . $e->getMessage());
		}
	}
}
