<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

interface MapperInterface
{
	/** @return list<array<string, string>> */
	public function getRecords(string $content): array;

	/** @return array<string, string|callable|null> */
	public function getMapping(): array;
}
