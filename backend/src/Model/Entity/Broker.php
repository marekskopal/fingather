<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity]
final class Broker
{
	#[Column(type: "primary")]
	private int $id;

	public function __construct(
		#[Column(type: "integer")]
		private int $userId,
		#[Column(type: "string")]
		private string $name,
		#[Column(type: "enum")]
		private BrokerImportTypeEnum $importType,
	) {
	}
}
