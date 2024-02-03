<?php

declare(strict_types=1);

namespace FinGather\Dto;

readonly class GroupWithAssetsDto extends GroupDto
{
	/**
	 * @param list<int> $assetIds
	 * @param list<AssetWithPropertiesDto> $assets
	 */
	public function __construct(int $id, int $userId, string $name, string $color, array $assetIds, public array $assets)
	{
		parent::__construct($id, $userId, $name, $color, $assetIds);
	}
}
