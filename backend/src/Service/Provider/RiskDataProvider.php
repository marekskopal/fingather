<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Dto\Enum\SamplingFrequencyEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\Cache;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheStorageEnum;
use FinGather\Service\DataCalculator\Dto\RiskDataDto;
use FinGather\Service\DataCalculator\RiskDataCalculator;

final readonly class RiskDataProvider implements RiskDataProviderInterface
{
	private Cache $cache;

	private const string CacheNamespace = 'risk-data';

	public function __construct(private RiskDataCalculator $riskDataCalculator, CacheFactory $cacheFactory,)
	{
		$this->cache = $cacheFactory->create(driver: CacheStorageEnum::Redis, namespace: self::CacheNamespace);
	}

	public function getRiskData(
		User $user,
		Portfolio $portfolio,
		RangeEnum $range,
		?Ticker $benchmarkTicker,
		?DateTimeImmutable $customRangeFrom,
		?DateTimeImmutable $customRangeTo,
		SamplingFrequencyEnum $samplingFrequency = SamplingFrequencyEnum::Daily,
	): RiskDataDto {
		$benchmarkTickerId = $benchmarkTicker !== null ? $benchmarkTicker->id : 0;
		$customRangeFromTs = $customRangeFrom !== null ? $customRangeFrom->getTimestamp() : 0;
		$customRangeToTs = $customRangeTo !== null ? $customRangeTo->getTimestamp() : 0;

		$key = $portfolio->id
			. '-' . $range->value
			. '-' . $samplingFrequency->value
			. '-' . $benchmarkTickerId
			. '-' . $customRangeFromTs
			. '-' . $customRangeToTs;

		/** @var RiskDataDto|null $riskData */
		$riskData = $this->cache->load($key);
		if ($riskData !== null) {
			return $riskData;
		}

		$riskData = $this->riskDataCalculator->calculate(
			user: $user,
			portfolio: $portfolio,
			range: $range,
			benchmarkTicker: $benchmarkTicker,
			customRangeFrom: $customRangeFrom,
			customRangeTo: $customRangeTo,
			samplingFrequency: $samplingFrequency,
		);

		$this->cache->save(key: $key, data: $riskData, user: $user, portfolio: $portfolio, expireSeconds: 3600);

		return $riskData;
	}

	public function deleteRiskData(?User $user = null, ?Portfolio $portfolio = null): void
	{
		$this->cache->clean($user, $portfolio);
	}
}
