<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

class NullMapper implements MapperInterface
{
	/** @return list<array<string, string>> */
	public function getRecords(string $content): array
	{
		return [];
	}

	/** @return array<string, string|callable> */
	public function getMapping(): array
	{
		return [];
	}
}
