<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\Enum\RangeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\DividendDataCalculator;
use FinGather\Service\DataCalculator\Dto\DividendDataIntervalDto;

class DividendDataProvider
{
	public function __construct(private readonly DividendDataCalculator $dividendDataCalculator,)
	{
	}

	/** @return list<DividendDataIntervalDto> */
	public function getDividendData(User $user, Portfolio $portfolio, RangeEnum $range): array
	{
		return $this->dividendDataCalculator->getDividendData($user, $portfolio, $range);
	}
}
