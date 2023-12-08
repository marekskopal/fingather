<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;

final readonly class BrokerDto
{
	public function __construct(
		public int $id,
		public int $userId,
		public string $name,
		public BrokerImportTypeEnum $importType,
	) {
	}
}
