<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface XlsxMapperInterface extends MapperInterface
{
	/** @return list<array<string, string>> */
	public function getRecordsFromSheet(Spreadsheet $spreadsheet): array;
}
