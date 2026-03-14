<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Dto\Enum\SamplingFrequencyEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\RiskDataDto;

interface RiskDataProviderInterface
{
	public function getRiskData(
		User $user,
		Portfolio $portfolio,
		RangeEnum $range,
		?Ticker $benchmarkTicker,
		?DateTimeImmutable $customRangeFrom,
		?DateTimeImmutable $customRangeTo,
		SamplingFrequencyEnum $samplingFrequency = SamplingFrequencyEnum::Daily,
	): RiskDataDto;

	public function deleteRiskData(?User $user = null, ?Portfolio $portfolio = null): void;
}
