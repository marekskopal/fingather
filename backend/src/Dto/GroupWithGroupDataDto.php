<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class GroupWithGroupDataDto extends GroupWithAssetsDto
{
	/** @param list<AssetDto> $assets */
	public function __construct(int $id, int $userId, string $name, array $assets, public float $percentage, public GroupDataDto $groupData)
	{
		parent::__construct($id, $userId, $name, $assets);
	}
}
