<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\Dto\MoneyValueDto;
use Override;

final class BinanceMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Binance;
	}

	/** @return list<array<string, string>> */
	#[Override]
	public function getRecords(string $content): array
	{
		$csvRecords = parent::getRecords($content);

		if (($csvRecords[1] ?? null) !== null && !array_key_exists('Remark', $csvRecords[1])) {
			return [];
		}

		$pairedRecords = [];

		$csvRecords = $this->sanitizeEmptyRemarks($csvRecords);

		foreach ($csvRecords as $record) {
			$recordsWithSameRemark = $this->findRecordsWithSameRemark($csvRecords, $record['Remark']);

			$price = $this->getPriceFromRecordsWithRemark($recordsWithSameRemark);

			if ($price->value === null || $price->currency === null) {
				continue;
			}

			if ($record['Coin'] === $price->currency) {
				continue;
			}

			if ($record['Operation'] === 'Transaction Related') {
				$record['Operation'] = (float) $record['Change'] < 0 ? 'Sell' : 'Buy';
			}

			$pairedRecords[] = [
				'User_ID' => $record['User_ID'],
				'UTC_Time' => $record['UTC_Time'],
				'Account' => $record['Account'],
				'Operation' => $record['Operation'],
				'Coin' => $record['Coin'],
				'Change' => $record['Change'],
				'Remark' => $record['Remark'],
				'Total' => $price->value,
				'Currency' => $price->currency,
			];
		}

		return $pairedRecords;
	}

	/**
	 * @param list<array<string, string>> $records
	 * @return list<array<string, string>>
	 */
	private function findRecordsWithSameRemark(array $records, string $remark): array
	{
		return array_values(array_filter($records, fn(array $record) => $record['Remark'] === $remark));
	}

	/**
	 * @param list<array<string, string>> $records
	 * @return list<array<string, string>>
	 */
	private function sanitizeEmptyRemarks(array $records): array
	{
		return array_map(
			function (array $record): array {
				if ($record['Remark'] !== '') {
					return $record;
				}

				$record['Remark'] = $record['UTC_Time'] . '-' . $record['Operation'];
				return $record;
			},
			$records,
		);
	}

	/** @param list<array<string, string>> $recordsWithSameRemark */
	private function getPriceFromRecordsWithRemark(array $recordsWithSameRemark): MoneyValueDto
	{
		$price = null;
		$currency = null;

		foreach ($recordsWithSameRemark as $recordWithSameRemark) {
			if (in_array($recordWithSameRemark['Coin'], ['USD', 'EUR'], true)) {
				$price = (string) abs((float) $recordWithSameRemark['Change']);
				$currency = $recordWithSameRemark['Coin'];
				break;
			}
		}

		return new MoneyValueDto($price, $currency);
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'Operation',
			created: 'UTC_Time',
			ticker: 'Coin',
			units: 'Change',
			total: 'Total',
			currency: 'Currency',
			importIdentifier: 'Remark',
		);
	}

	#[Override]
	public function check(string $content, string $fileName): bool
	{
		if (!parent::check($content, $fileName)) {
			return false;
		}

		$records = $this->getRecords($content);

		return
			// Check if there is at least one record (header is not counted)
			isset($records[1]) &&
			array_key_exists('User_ID', $records[1]) &&
			array_key_exists('UTC_Time', $records[1]) &&
			array_key_exists('Account', $records[1]) &&
			array_key_exists('Operation', $records[1]) &&
			array_key_exists('Coin', $records[1]) &&
			array_key_exists('Change', $records[1]) &&
			array_key_exists('Remark', $records[1]);
	}

	/** @return list<int> */
	#[Override]
	public function getAllowedMarketIds(): array
	{
		return [83];
	}
}
