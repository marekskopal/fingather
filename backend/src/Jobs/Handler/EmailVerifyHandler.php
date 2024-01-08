<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\EmailVerifyDto;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class EmailVerifyHandler implements JobHandler
{
	public function handle(ReceivedTaskInterface $task): void
	{
		/** @var EmailVerifyDto $emailVerify */
		$emailVerify = $task->getPayload();

		$transport = Transport::fromDsn('smtp://localhost');
		$mailer = new Mailer($transport);

		$from = (string) getenv('EMAIL_FROM');

		$email = (new Email())
			->from($from)
			->to($emailVerify->email)
			->subject('FinGather - Verify your email.')
			->html($this->getEmailText());

		$mailer->send($email);
	}

	private function getEmailText(): string
	{
		$host = getenv('PROXY_HOST');

		return '
			<h1>FinGther - Verify your email</h1>
			<p>Please verify you email on <a href="' . $host . '">' . $host . '</a></p>
		';
	}
}
