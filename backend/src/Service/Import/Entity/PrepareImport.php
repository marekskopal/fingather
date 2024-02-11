<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Entity;

use FinGather\Model\Entity\Import;

readonly class PrepareImport
{
	/**
	 * @param array<string, PrepareImportTicker> $notFoundTickers
	 * @param array<string, PrepareImportTicker> $multipleFoundTickers
	 * @param array<string, PrepareImportTicker> $okFoundTickers
	 */
	public function __construct(
		public Import $import,
		public array $notFoundTickers,
		public array $multipleFoundTickers,
		public array $okFoundTickers,
	) {
	}
}
