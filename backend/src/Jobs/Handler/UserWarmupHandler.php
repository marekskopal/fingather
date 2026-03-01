<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Jobs\Message\ReceivedMessageInterface;
use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Task\TaskServiceInterface;
use FinGather\Service\Warmup\Dto\UserWarmupDto;
use FinGather\Service\Warmup\UserWarmup;
use FinGather\Utils\BenchmarkUtils;
use Psr\Log\LoggerInterface;

class UserWarmupHandler implements JobHandler
{
	public function __construct(
		private readonly UserWarmup $userWarmup,
		private readonly UserProvider $userProvider,
		private readonly LoggerInterface $logger,
		private readonly TaskServiceInterface $taskService,
	)
	{
	}

	public function handle(ReceivedMessageInterface $message): void
	{
		$userWarmup = $this->taskService->getPayloadDto($message, UserWarmupDto::class);

		$user = $this->userProvider->getUser($userWarmup->userId);
		if ($user === null) {
			return;
		}

		$this->logger->info('User warmup started', ['userId' => $userWarmup->userId]);

		$benchmarkTime = BenchmarkUtils::benchmark(fn() => $this->userWarmup->warmup($user));

		$this->logger->info('User warmup ended', ['userId' => $userWarmup->userId, 'benchmarkTime' => $benchmarkTime]);
	}
}
