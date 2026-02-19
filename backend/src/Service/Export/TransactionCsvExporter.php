<?php

declare(strict_types=1);

namespace FinGather\Service\Export;

use FinGather\Model\Entity\Transaction;
use Iterator;
use League\Csv\Writer;

final readonly class TransactionCsvExporter
{
	/** @param Iterator<Transaction> $transactions */
	public function export(Iterator $transactions): string
	{
		$csv = Writer::fromString();
		$csv->insertOne([
			'Date',
			'Type',
			'Ticker Symbol',
			'Ticker Name',
			'Units',
			'Price',
			'Currency',
			'Tax',
			'Tax Currency',
			'Fee',
			'Fee Currency',
			'Notes',
			'Import Identifier',
		]);

		foreach ($transactions as $transaction) {
			$csv->insertOne([
				$transaction->actionCreated->format('Y-m-d H:i:s'),
				$transaction->actionType->value,
				$transaction->asset->ticker->ticker,
				$transaction->asset->ticker->name,
				$transaction->units->toString(),
				$transaction->price->toString(),
				$transaction->currency->code,
				$transaction->tax->toString(),
				$transaction->taxCurrency->code,
				$transaction->fee->toString(),
				$transaction->feeCurrency->code,
				$transaction->notes ?? '',
				$transaction->importIdentifier ?? '',
			]);
		}

		$tempFile = tempnam(sys_get_temp_dir(), 'transaction_export_') . '.csv';
		file_put_contents($tempFile, $csv->toString());

		return $tempFile;
	}
}
