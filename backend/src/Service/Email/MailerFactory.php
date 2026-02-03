<?php

declare(strict_types=1);

namespace FinGather\Service\Email;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

final readonly class MailerFactory
{
	public function create(): Mailer
	{
		$host = (string) getenv('SMTP_HOST');
		$port = (string) getenv('SMTP_PORT');
		$user = (string) getenv('SMTP_USER');
		$password = (string) getenv('SMTP_PASSWORD');

		$transport = Transport::fromDsn('smtp://' . ($user !== '' ? $user . ':' . $password . '@' : '') . $host . ':' . $port);

		return new Mailer($transport);
	}
}
