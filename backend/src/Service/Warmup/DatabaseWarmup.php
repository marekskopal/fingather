<?php

declare(strict_types=1);

namespace FinGather\Service\Warmup;

use FinGather\Service\Provider\UserProviderInterface;
use FinGather\Service\Queue\Enum\QueueEnum;
use FinGather\Service\Queue\QueuePublisher;
use FinGather\Service\Warmup\Dto\UserWarmupDto;

final readonly class DatabaseWarmup
{
	public function __construct(
		private UserProviderInterface $userProvider,
		private QueuePublisher $queuePublisher,
		private UserWarmup $userWarmup,
	) {
	}

	public function warmup(): void
	{
		$users = $this->userProvider->getUsers();

		foreach ($users as $user) {
			$this->userWarmup->warmup($user);
		}
	}

	public function warmupAsync(): void
	{
		$users = $this->userProvider->getUsers();

		foreach ($users as $user) {
			$this->queuePublisher->publishMessage(
				new UserWarmupDto($user->id),
				QueueEnum::UserWarmup,
				delay: 1,
			);
		}
	}
}
