<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

interface XlsxMapperInterface extends MapperInterface
{
	public function getSheetIndex(): int;
}
