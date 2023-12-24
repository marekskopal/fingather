<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

interface MapperInterface
{
	/** @return array<string, string|callable> */
	public function getCsvMapping(): array;

	/** @return array<string, string> */
	public function getTickerMapping(): array;
}
