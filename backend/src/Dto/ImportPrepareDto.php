<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Service\Import\Entity\PrepareImport;
use FinGather\Service\Import\Entity\PrepareImportTicker;
use Ramsey\Uuid\UuidInterface;

final readonly class ImportPrepareDto
{
	/**
	 * @param list<ImportPrepareTickerDto> $notFoundTickers
	 * @param list<ImportPrepareTickerDto> $multipleFoundTickers
	 * @param list<ImportPrepareTickerDto> $okFoundTickers
	 */
	public function __construct(
		public int $importId,
		public UuidInterface $uuid,
		public array $notFoundTickers,
		public array $multipleFoundTickers,
		public array $okFoundTickers,
	) {
	}

	public static function fromImportPrepare(PrepareImport $prepareImport): self
	{
		return new self(
			importId: $prepareImport->import->getId(),
			uuid: $prepareImport->import->getUuid(),
			notFoundTickers: array_map(
				fn (PrepareImportTicker $item): ImportPrepareTickerDto => ImportPrepareTickerDto::fromImportPrepareTicker($item),
				array_values($prepareImport->notFoundTickers),
			),
			multipleFoundTickers: array_map(
				fn (PrepareImportTicker $item): ImportPrepareTickerDto => ImportPrepareTickerDto::fromImportPrepareTicker($item),
				array_values($prepareImport->multipleFoundTickers),
			),
			okFoundTickers: array_map(
				fn (PrepareImportTicker $item): ImportPrepareTickerDto => ImportPrepareTickerDto::fromImportPrepareTicker($item),
				array_values($prepareImport->okFoundTickers),
			),
		);
	}
}
