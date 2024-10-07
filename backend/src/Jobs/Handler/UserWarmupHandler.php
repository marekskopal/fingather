<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Warmup\Dto\UserWarmupDto;
use FinGather\Service\Warmup\UserWarmup;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use function Safe\json_decode;

class UserWarmupHandler implements JobHandler
{
	public function __construct(private readonly UserWarmup $userWarmup, private readonly UserProvider $userProvider)
	{
	}

	public function handle(ReceivedTaskInterface $task): void
	{
		/**
		 * @var array{
		 *     userId: int
		 * } $payload
		 */
		$payload = json_decode($task->getPayload(), assoc: true);

		$userWarmup = UserWarmupDto::fromArray($payload);

		$user = $this->userProvider->getUser($userWarmup->userId);
		if ($user === null) {
			return;
		}

		$this->userWarmup->warmup($user);
	}
}
