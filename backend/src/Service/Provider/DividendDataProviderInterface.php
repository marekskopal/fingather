<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\Enum\RangeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\DividendDataIntervalDto;

interface DividendDataProviderInterface
{
	/** @return list<DividendDataIntervalDto> */
	public function getDividendData(User $user, Portfolio $portfolio, RangeEnum $range): array;
}
