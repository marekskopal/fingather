<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class AssetsWithPropertiesDto
{
	/**
	 * @param AssetWithPropertiesDto[] $openAssets
	 * @param AssetWithPropertiesDto[] $closedAssets
	 * @param AssetDto[] $watchedAssets
	 */
	public function __construct(public array $openAssets, public array $closedAssets, public array $watchedAssets,)
	{
	}
}
