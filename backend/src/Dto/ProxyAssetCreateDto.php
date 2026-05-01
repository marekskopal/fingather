<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\TickerTypeEnum;

/**
 * @implements ArrayFactoryInterface<array{
 *     tickerType: string,
 *     tickerId: int,
 * }>
 */
final readonly class ProxyAssetCreateDto implements ArrayFactoryInterface
{
	public function __construct(public TickerTypeEnum $tickerType, public int $tickerId,)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(
			tickerType: TickerTypeEnum::from($data['tickerType']),
			tickerId: $data['tickerId'],
		);
	}
}
