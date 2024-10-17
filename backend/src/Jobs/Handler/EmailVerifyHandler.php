<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\EmailVerifyDto;
use FinGather\Email\VerifyEmail;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use function Safe\json_decode;

final class EmailVerifyHandler implements JobHandler
{
	public function __construct(private readonly LoggerInterface $logger)
	{
	}

	public function handle(ReceivedTaskInterface $task): void
	{
		/**
		 * @var array{
		 *     user: array{
		 *         id: int,
		 *         email: string,
		 *         name: string,
		 *         defaultCurrencyId: int,
		 *         role: value-of<UserRoleEnum>,
		 *     },
		 *     token: string,
		 * } $payload
		 */
		$payload = json_decode($task->getPayload(), assoc: true);

		$emailVerify = EmailVerifyDto::fromArray($payload);

		$host = (string) getenv('SMTP_HOST');
		$port = (string) getenv('SMTP_PORT');
		$user = (string) getenv('SMTP_USER');
		$password = (string) getenv('SMTP_PASSWORD');

		$transport = Transport::fromDsn('smtp://' . ($user !== '' ? $user . ':' . $password . '@' : '') . $host . ':' . $port);
		$mailer = new Mailer($transport);

		$from = (string) getenv('EMAIL_FROM');

		$email = (new Email())
			->from($from)
			->to($emailVerify->user->email)
			->subject('FinGather - Verify your email.')
			->html(VerifyEmail::getHtml($emailVerify));

		$this->logger->info('Send verify email to: ' . $emailVerify->user->email);

		try {
			$mailer->send($email);
		} catch (\Throwable $e) {
			$this->logger->error('Error sending verify email: ' . $e->getMessage());
		}
	}
}
