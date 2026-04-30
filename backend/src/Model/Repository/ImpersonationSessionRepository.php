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

	public function findSession(int $id): ?ImpersonationSession
	{
		return $this->findOne([
			'id' => $id,
		]);
	}

	/** @return iterable<ImpersonationSession> */
	public function findRecentSessions(int $limit = 100): iterable
	{
		return $this->select()
			->orderBy('id', 'DESC')
			->limit($limit)
			->fetchAll();
	}
}
