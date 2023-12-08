<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;

final readonly class CredentialsDto
{
	public function __construct(
		#[\SensitiveParameter]
		public string $email,
		#[\SensitiveParameter]
		public string $password,
	) {
	}
}
