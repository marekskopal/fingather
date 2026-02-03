<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\EmailVerifyDto;
use FinGather\Service\Email\EmailFactory;
use FinGather\Service\Email\MailerFactory;
use FinGather\Service\Task\TaskServiceInterface;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;

final class EmailVerifyHandler implements JobHandler
{
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly TaskServiceInterface $taskService,
		private readonly MailerFactory $mailerFactory,
		private readonly EmailFactory $emailFactory,
	) {
	}

	public function handle(ReceivedTaskInterface $task): void
	{
		$emailVerify = $this->taskService->getPayloadDto($task, EmailVerifyDto::class);

		$mailer = $this->mailerFactory->create();

		$email = $this->emailFactory->createEmailVerifyEmail($emailVerify);

		$this->logger->info('Send verify email to: ' . $emailVerify->user->email);

		try {
			$mailer->send($email);
		} catch (\Throwable $e) {
			$this->logger->error('Error sending verify email: ' . $e->getMessage());
		}
	}
}
