<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use Safe\DateTimeImmutable;
use function Safe\json_decode;

final readonly class TransactionCreateDto
{
	public function __construct(
		public int $assetId,
		public int $brokerId,
		public TransactionActionTypeEnum $actionType,
		public DateTimeImmutable $actionCreated,
		public Decimal $units,
		public Decimal $price,
		public int $currencyId,
		public ?Decimal $tax,
		public int $taxCurrencyId,
		public ?Decimal $fee,
		public int $feeCurrencyId,
		public ?string $notes,
		public ?string $importIdentifier,
	) {
	}

	/** @param array{
	 *     assetId: int,
	 *     brokerId: int,
	 *     actionType: value-of<TransactionActionTypeEnum>,
	 *     actionCreated: string,
	 *     units: string,
	 *     price: string,
	 *     currencyId: int,
	 *     tax: string,
	 *     taxCurrencyId: int,
	 *     fee: string,
	 *     feeCurrencyId: int,
	 *     notes?: string|null,
	 *     importIdentifier?: string|null
	 * } $data
	 */
	private static function fromArray(array $data): self
	{
		return new self(
			assetId: $data['assetId'],
			brokerId: $data['brokerId'],
			actionType: TransactionActionTypeEnum::from($data['actionType']),
			actionCreated: new DateTimeImmutable($data['actionCreated']),
			units: new Decimal($data['units']),
			price: new Decimal($data['price']),
			currencyId: $data['currencyId'],
			tax: new Decimal($data['tax']),
			taxCurrencyId: $data['taxCurrencyId'],
			fee: new Decimal($data['fee']),
			feeCurrencyId: $data['feeCurrencyId'],
			notes: $data['notes'] ?? null,
			importIdentifier: $data['importIdentifier'] ?? null,
		);
	}

	public static function fromJson(string $json): self
	{
		/** @var array{
		 *     assetId: int,
		 *     brokerId: int,
		 *     actionType: value-of<TransactionActionTypeEnum>,
		 *     actionCreated: string,
		 *     units: string,
		 *     price: string,
		 *     currencyId: int,
		 *     tax: string,
		 *     taxCurrencyId: int,
		 *     fee: string,
		 *     feeCurrencyId: int,
		 *     notes?: string|null,
		 *     importIdentifier?: string|null
		 * } $data
		 */
		$data = json_decode($json, assoc: true);
		return self::fromArray($data);
	}
}
