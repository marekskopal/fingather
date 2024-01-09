<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\EmailVerifyDto;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use function Safe\json_decode;

class EmailVerifyHandler implements JobHandler
{
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

		$transport = Transport::fromDsn('smtp://' . $host . ':' . $port);
		$mailer = new Mailer($transport);

		$from = (string) getenv('EMAIL_FROM');

		$email = (new Email())
			->from($from)
			->to($emailVerify->user->email)
			->subject('FinGather - Verify your email.')
			->html($this->getEmailText());

		$mailer->send($email);
	}

	private function getEmailText(EmailVerifyDto $emailVerify): string
	{
		$host = (string) getenv('PROXY_HOST');

		$verifyUrl = 'https://' . $host . '/email-verify/' . $emailVerify->token;

		return '
			<h1>FinGather - Verify your email</h1>
			<p>Please verify you email on <a href="' . $verifyUrl . '">' . $verifyUrl . '</a></p>
		';
	}
}
