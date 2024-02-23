<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

interface CsvMapperInterface extends MapperInterface
{
	public function getCsvDelimiter(): string;
}
