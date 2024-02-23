<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

class NullMapper implements MapperInterface
{
	/** @return array<string, string|callable> */
	public function getCsvMapping(): array
	{
		return [];
	}

	public function getCsvDelimiter(): string
	{
		return ',';
	}
}
