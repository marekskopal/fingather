<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;

/**
 * @implements ArrayFactoryInterface<array{
 *     name: string,
 *     import_type: string,
 * }>
 */
final readonly class BrokerCreateDto implements ArrayFactoryInterface
{
	public function __construct(public string $name, public BrokerImportTypeEnum $importType)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(
			name: $data['name'],
			importType: BrokerImportTypeEnum::from($data['import_type']),
		);
	}
}
