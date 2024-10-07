<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class GroupWithGroupDataDto extends AbstractGroupWithGroupDataDto
{
	/**
	 * @param list<int> $assetIds
	 * @param list<AssetWithPropertiesDto> $assets
	 */
	public function __construct(
		int $id,
		int $userId,
		string $name,
		public string $color,
		public array $assetIds,
		public array $assets,
		float $percentage,
		GroupDataDto $groupData,
	)
	{
		parent::__construct($id, $userId, $name, $percentage, $groupData);
	}
}
