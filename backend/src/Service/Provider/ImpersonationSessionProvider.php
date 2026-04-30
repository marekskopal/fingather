<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Enum\ImpersonationTerminationReasonEnum;
use FinGather\Model\Entity\ImpersonationSession;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\ImpersonationSessionRepository;

final readonly class ImpersonationSessionProvider implements ImpersonationSessionProviderInterface
{
	public function __construct(private ImpersonationSessionRepository $impersonationSessionRepository)
	{
	}

	public function getActiveSession(int $id): ?ImpersonationSession
	{
		return $this->impersonationSessionRepository->findActiveSession($id);
	}

	public function getSession(int $id): ?ImpersonationSession
	{
		return $this->impersonationSessionRepository->findSession($id);
	}

	public function createSession(User $admin, User $target, string $ipAddress, string $userAgent,): ImpersonationSession
	{
		$session = new ImpersonationSession(
			adminUser: $admin,
			targetUser: $target,
			startedAt: new DateTimeImmutable(),
			endedAt: null,
			ipAddress: substr($ipAddress, 0, 45),
			userAgent: substr($userAgent, 0, 255),
			terminationReason: null,
		);

		$this->impersonationSessionRepository->persist($session);

		return $session;
	}

	public function endSession(ImpersonationSession $session, ImpersonationTerminationReasonEnum $reason,): void
	{
		if ($session->endedAt !== null) {
			return;
		}

		$session->endedAt = new DateTimeImmutable();
		$session->terminationReason = $reason;

		$this->impersonationSessionRepository->persist($session);
	}

	/** @return iterable<ImpersonationSession> */
	public function getRecentSessions(int $limit = 100): iterable
	{
		return $this->impersonationSessionRepository->findRecentSessions($limit);
	}
}
