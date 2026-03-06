<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\PasswordResetQueueDto;
use FinGather\Jobs\Message\ReceivedMessageInterface;
use FinGather\Service\Email\EmailFactory;
use FinGather\Service\Email\MailerFactory;
use FinGather\Service\Task\TaskServiceInterface;
use Psr\Log\LoggerInterface;

final class PasswordResetHandler implements JobHandler
{
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly TaskServiceInterface $taskService,
		private readonly MailerFactory $mailerFactory,
		private readonly EmailFactory $emailFactory,
	) {
	}

	public function handle(ReceivedMessageInterface $message): void
	{
		$passwordReset = $this->taskService->getPayloadDto($message, PasswordResetQueueDto::class);

		$mailer = $this->mailerFactory->create();

		$email = $this->emailFactory->createPasswordResetEmail($passwordReset);

		$this->logger->info('Send password reset email to: ' . $passwordReset->user->email);

		try {
			$mailer->send($email);
		} catch (\Throwable $e) {
			$this->logger->error('Error sending password reset email: ' . $e->getMessage());
		}
	}
}
