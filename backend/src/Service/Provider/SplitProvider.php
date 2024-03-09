<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Split;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Repository\SplitRepository;
use MarekSkopal\TwelveData\TwelveData;

class SplitProvider
{
	public function __construct(private readonly SplitRepository $splitRepository, private readonly TwelveData $twelveData)
	{
	}

	/** @return iterable<Split> */
	public function getSplits(Ticker $ticker): iterable
	{
		return $this->splitRepository->findSplits($ticker->getId());
	}

	public function getSplit(Ticker $ticker, ?DateTimeImmutable $date = null): ?Split
	{
		return $this->splitRepository->findSplit($ticker->getId(), $date);
	}

	public function createSplit(Ticker $ticker, DateTimeImmutable $date, Decimal $factor): Split
	{
		$split = new Split(ticker: $ticker, date: $date, factor: $factor);
		$this->splitRepository->persist($split);

		return $split;
	}

	public function updateSplits(Ticker $ticker): void
	{
		$splits = $this->twelveData->getFundamentals()->splits(
			symbol: $ticker->getTicker(),
			micCode: $ticker->getMarket()->getMic(),
		);

		foreach ($splits->splits as $split) {
			if ($this->getSplit(ticker: $ticker, date: $split->date) !== null) {
				continue;
			}

			$this->createSplit(
				ticker: $ticker,
				date: $split->date,
				factor: (new Decimal((string) $split->fromFactor))->div(new Decimal((string) $split->toFactor)),
			);
		}
	}
}
