<?php

declare(strict_types=1);

namespace FinGather\Dto;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;

/**
 * @implements ArrayFactoryInterface<array{
 *     assetId: int,
 *     brokerId: int|null,
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
 * }>
 */
final readonly class TransactionCreateDto implements ArrayFactoryInterface
{
	public function __construct(
		public int $assetId,
		public ?int $brokerId,
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

	public static function fromArray(array $data): static
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
}
