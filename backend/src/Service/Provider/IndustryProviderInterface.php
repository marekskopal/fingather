<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

interface IndustryProviderInterface
{
	/** @return array<int, Industry> */
	public function getIndustriesFromAssets(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array;
}
