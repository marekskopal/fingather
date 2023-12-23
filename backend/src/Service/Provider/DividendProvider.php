<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Dividend;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\DividendRepository;
use Safe\DateTimeImmutable;

class DividendProvider
{
	public function __construct(private readonly DividendRepository $dividendRepository)
	{
	}

	/** @return array<Dividend> */
	public function getDividends(User $user, ?DateTimeImmutable $dateTime = null): array
	{
		return $this->dividendRepository->findDividends($user->getId(), $dateTime);
	}

	/** @return array<Dividend> */
	public function getAssetDividends(User $user, Asset $asset, ?DateTimeImmutable $dateTime = null): array
	{
		return $this->dividendRepository->findAssetDividends($user->getId(), $asset->getId(), $dateTime);
	}
}
