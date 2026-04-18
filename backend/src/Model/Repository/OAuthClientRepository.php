<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\OAuthClient;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<OAuthClient> */
final class OAuthClientRepository extends AbstractRepository
{
	public function findByClientId(string $clientId): ?OAuthClient
	{
		return $this->select()->where(['client_id' => $clientId])->fetchOne();
	}
}
