<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\EmailVerifyDto;
use FinGather\Model\Entity\EmailVerify;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\EmailVerifyRepository;
use FinGather\Service\Queue\Enum\QueueEnum;
use FinGather\Service\Queue\QueuePublisher;
use Ramsey\Uuid\Uuid;

final readonly class EmailVerifyProvider
{
	public function __construct(private EmailVerifyRepository $emailVerifyRepository, private QueuePublisher $queuePublisher,)
	{
	}

	public function getEmailVerify(string $token): ?EmailVerify
	{
		return $this->emailVerifyRepository->findEmailVerifyByToken($token);
	}

	public function createEmailVerify(User $user,): EmailVerify
	{
		$emailVerify = new EmailVerify(
			user: $user,
			token: (string) Uuid::uuid4(),
		);
		$this->emailVerifyRepository->persist($emailVerify);

		$this->queuePublisher->publishMessage(EmailVerifyDto::fromEntity($emailVerify), QueueEnum::EmailVerify);

		return $emailVerify;
	}
}
