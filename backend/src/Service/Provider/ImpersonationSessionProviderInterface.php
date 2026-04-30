<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Enum\ImpersonationTerminationReasonEnum;
use FinGather\Model\Entity\ImpersonationSession;
use FinGather\Model\Entity\User;

interface ImpersonationSessionProviderInterface
{
	public function getActiveSession(int $id): ?ImpersonationSession;

	public function getSession(int $id): ?ImpersonationSession;

	public function createSession(User $admin, User $target, string $ipAddress, string $userAgent,): ImpersonationSession;

	public function endSession(ImpersonationSession $session, ImpersonationTerminationReasonEnum $reason,): void;

	/** @return iterable<ImpersonationSession> */
	public function getRecentSessions(int $limit = 100): iterable;
}
