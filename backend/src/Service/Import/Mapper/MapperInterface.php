<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;

interface MapperInterface
{
	public function getImportType(): BrokerImportTypeEnum;

	/** @return list<array<string, string>> */
	public function getRecords(string $content): array;

	public function getMapping(): MappingDto;

	/** @return list<int>|null */
	public function getAllowedMarketIds(): ?array;
}
