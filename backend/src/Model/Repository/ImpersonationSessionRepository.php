<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\ImpersonationSession;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<ImpersonationSession> */
final class ImpersonationSessionRepository extends AbstractRepository
{
	public function findActiveSession(int $id): ?ImpersonationSession
	{
		$session = $this->findOne([
			'id' => $id,
		]);
		if ($session === null || $session->endedAt !== null) {
			return null;
		}

		return $session;
	}

	/** @return list<ImpersonationSession> */
	public function findRecentSessions(int $limit = 100): array
	{
		return $this->select()
			->orderBy('id', 'DESC')
			->limit($limit)
			->fetchAll();
	}
}
