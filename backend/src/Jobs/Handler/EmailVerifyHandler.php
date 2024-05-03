<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\EmailVerifyDto;
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
			->html($this->getEmailText($emailVerify));

		$this->logger->info('Send verify email to: ' . $emailVerify->user->email);

		$mailer->send($email);
	}

	private function getEmailText(EmailVerifyDto $emailVerify): string
	{
		$host = (string) getenv('PROXY_HOST');
		$port = (int) getenv('PROXY_PORT_SSL');

		$verifyUrl = 'https://' . $host . ($port !== 443 ? ':' . $port : '') . '/email-verify/' . $emailVerify->token;

		return '
			<h1>FinGather - Verify your email</h1>
			<p>Please verify you email on <a href="' . $verifyUrl . '">' . $verifyUrl . '</a></p>
		';
	}
}
