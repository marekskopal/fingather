<?php

declare(strict_types=1);

namespace FinGather\Service\Update;

use Decimal\Decimal;
use FinGather\Model\Entity\Ticker;
use FinGather\Service\Provider\SplitProvider;
use MarekSkopal\TwelveData\Enum\RangeEnum;
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;

final class SplitUpdater
{
	public function __construct(private readonly SplitProvider $splitProvider, private readonly TwelveData $twelveData)
	{
	}

	public function updateSplits(Ticker $ticker): void
	{
		try {
			$splits = $this->twelveData->getFundamentals()->splits(
				symbol: $ticker->ticker,
				micCode: $ticker->market->mic,
				range: RangeEnum::Full,
			);
		} catch (NotFoundException) {
			return;
		}

		$splitCreated = false;

		foreach ($splits->splits as $split) {
			if ($this->splitProvider->getSplit(ticker: $ticker, date: $split->date) !== null) {
				continue;
			}

			$this->splitProvider->createSplit(
				ticker: $ticker,
				date: $split->date,
				factor: (new Decimal((string) $split->fromFactor))->div(new Decimal((string) $split->toFactor)),
			);

			$splitCreated = true;
		}

		if ($splitCreated) {
			$this->splitProvider->cleanCache($ticker);
		}
	}
}
