<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;

interface MapperInterface
{
	public function getImportType(): BrokerImportTypeEnum;

	/** @return list<array<string, string>> */
	public function getRecords(string $content): array;

	/** @return array<string, string|callable|null> */
	public function getMapping(): array;

	public function check(string $content, string $fileName): bool;

	/** @return list<int>|null */
	public function getAllowedMarketIds(): ?array;
}
