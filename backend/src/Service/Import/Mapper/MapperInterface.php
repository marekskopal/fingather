<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

interface MapperInterface
{
	public function getMapping(): array;
}
