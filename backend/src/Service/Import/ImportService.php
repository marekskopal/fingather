<?php

declare(strict_types=1);

namespace FinGather\Service\Import;

use FinGather\Dto\BrokerDto;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\TransactionRepository;
use FinGather\Model\Repository\UserRepository;
use FinGather\Service\Import\Entity\TransactionRecord;
use FinGather\Service\Import\Mapper\MapperInterface;
use FinGather\Service\Import\Mapper\RevolutMapper;
use FinGather\Service\Import\Mapper\Trading212Mapper;
use FinGather\Service\Provider\TickerProvider;
use League\Csv\Reader;

final class ImportService
{
	public function __construct(
		private readonly TransactionRepository $transactionRepository,
		private readonly TickerProvider $tickerProvider,
		private readonly AssetRepository $assetRepository,
		private readonly UserRepository $userRepository,
	) {
	}

	public function importCsv(BrokerDto $broker, string $csvContent): void
	{
		$csv = Reader::createFromString($csvContent);
		$csv->setHeaderOffset(0);

		$importMapper = $this->getImportMapper($broker->importType);

		$user = $this->userRepository->findUserById($broker->userId);

		$records = $csv->getRecords();
		foreach ($records as $record) {
			$transactionRecord = $this->mapTransactionRecord($importMapper, $record);

			if (
				isset($transactionRecord->importIdentifier)
				&& $this->transactionRepository->findTransactionByIdentifier($broker->id, $transactionRecord->importIdentifier) !== null
			) {
				continue;
			}

			if (!isset($transactionRecord->ticker)) {
				continue;
			}

			$ticker = $this->tickerProvider->getOrCreateTicker($transactionRecord->ticker);
			if ($ticker === null) {
				continue;
			}

			$asset = $this->assetRepository->findAssetByTickerId($user->getId(), $ticker->getId());
			if ($asset === null) {
				$asset = new Asset(
					user: $user,
					ticker: $ticker,
					group: null,
					transactions: [],
				);
				$this->assetRepository->persist($asset);
			}

			//$transaction = new Transaction()
		}
	}

	private function mapTransactionRecord(MapperInterface $mapper, array $csvRecord): TransactionRecord
	{
		$transactionRecord = new TransactionRecord();

		foreach ($mapper->getMapping() as $attribute => $recordKey) {
			$transactionRecord->{$attribute} = $csvRecord[$recordKey] ?? null;
		}

		return $transactionRecord;
	}

	private function getImportMapper(BrokerImportTypeEnum $importType): MapperInterface
	{
		return match ($importType) {
			BrokerImportTypeEnum::Revolut => new RevolutMapper(),
			BrokerImportTypeEnum::Trading212 => new Trading212Mapper(),
		};
	}
}
