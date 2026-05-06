<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     userId: int,
 *     priceAlertId: int,
 *     currentValue: string,
 * }>
 */
final readonly class PriceAlertNotificationDto implements ArrayFactoryInterface
{
	public function __construct(
		public int $userId,
		public int $priceAlertId,
		public string $currentValue,
	) {
	}

	public static function fromArray(array $data): static
	{
		return new self(
			userId: $data['userId'],
			priceAlertId: $data['priceAlertId'],
			currentValue: $data['currentValue'],
		);
	}
}
