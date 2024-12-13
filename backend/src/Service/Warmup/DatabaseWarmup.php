<?php

declare(strict_types=1);

namespace FinGather\Service\Warmup;

use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Queue\Enum\QueueEnum;
use FinGather\Service\Queue\QueuePublisher;
use FinGather\Service\Warmup\Dto\UserWarmupDto;

final class DatabaseWarmup
{
	public function __construct(
		private readonly UserProvider $userProvider,
		private readonly QueuePublisher $queuePublisher,
		private readonly UserWarmup $userWarmup,
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
