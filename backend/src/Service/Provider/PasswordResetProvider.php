<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\PasswordResetQueueDto;
use FinGather\Model\Entity\PasswordReset;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\PasswordResetRepository;
use FinGather\Service\Queue\Enum\QueueEnum;
use FinGather\Service\Queue\QueuePublisher;
use Ramsey\Uuid\Uuid;

class PasswordResetProvider
{
	public function __construct(
		private readonly PasswordResetRepository $passwordResetRepository,
		private readonly QueuePublisher $queuePublisher,
	) {
	}

	public function getPasswordReset(string $token): ?PasswordReset
	{
		return $this->passwordResetRepository->findPasswordResetByToken($token);
	}

	public function createPasswordReset(User $user): PasswordReset
	{
		$passwordReset = new PasswordReset(
			user: $user,
			token: (string) Uuid::uuid4(),
			createdAt: new DateTimeImmutable(),
		);
		$this->passwordResetRepository->persist($passwordReset);

		$this->queuePublisher->publishMessage(PasswordResetQueueDto::fromEntity($passwordReset), QueueEnum::PasswordReset);

		return $passwordReset;
	}

	public function deletePasswordReset(PasswordReset $passwordReset): void
	{
		$this->passwordResetRepository->delete($passwordReset);
	}
}
