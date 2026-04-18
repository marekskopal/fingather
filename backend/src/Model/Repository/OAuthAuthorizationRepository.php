<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\OAuthAuthorization;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<OAuthAuthorization> */
final class OAuthAuthorizationRepository extends AbstractRepository
{
	public function findByAuthorizationCodeHash(string $hash): ?OAuthAuthorization
	{
		return $this->select()->where(['authorization_code_hash' => $hash])->fetchOne();
	}

	public function findByAccessTokenHash(string $hash): ?OAuthAuthorization
	{
		return $this->select()->where(['access_token_hash' => $hash])->fetchOne();
	}

	public function findByRefreshTokenHash(string $hash): ?OAuthAuthorization
	{
		return $this->select()->where(['refresh_token_hash' => $hash])->fetchOne();
	}
}
