<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Repository\TickerFundamentalRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: TickerFundamentalRepository::class)]
class TickerFundamental extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: Ticker::class)]
		public readonly Ticker $ticker,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $marketCapitalization,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $enterpriseValue,
		#[Column(type: 'float', nullable: true)]
		public ?float $trailingPe,
		#[Column(type: 'float', nullable: true)]
		public ?float $forwardPe,
		#[Column(type: 'float', nullable: true)]
		public ?float $pegRatio,
		#[Column(type: 'float', nullable: true)]
		public ?float $priceToSalesTtm,
		#[Column(type: 'float', nullable: true)]
		public ?float $priceToBookMrq,
		#[Column(type: 'float', nullable: true)]
		public ?float $enterpriseToRevenue,
		#[Column(type: 'float', nullable: true)]
		public ?float $enterpriseToEbitda,
		#[Column(type: 'date', nullable: true)]
		public ?DateTimeImmutable $fiscalYearEnds,
		#[Column(type: 'date', nullable: true)]
		public ?DateTimeImmutable $mostRecentQuarter,
		#[Column(type: 'float', nullable: true)]
		public ?float $profitMargin,
		#[Column(type: 'float', nullable: true)]
		public ?float $operatingMargin,
		#[Column(type: 'float', nullable: true)]
		public ?float $returnOnAssetsTtm,
		#[Column(type: 'float', nullable: true)]
		public ?float $returnOnEquityTtm,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $revenueTtm,
		#[Column(type: 'float', nullable: true)]
		public ?float $revenuePerShareTtm,
		#[Column(type: 'float', nullable: true)]
		public ?float $quarterlyRevenueGrowth,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $grossProfitTtm,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $ebitda,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $netIncomeToCommonTtm,
		#[Column(type: 'float', nullable: true)]
		public ?float $dilutedEpsTtm,
		#[Column(type: 'float', nullable: true)]
		public ?float $quarterlyEarningsGrowthYoy,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $totalCashMrq,
		#[Column(type: 'float', nullable: true)]
		public ?float $totalCashPerShareMrq,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $totalDebtMrq,
		#[Column(type: 'float', nullable: true)]
		public ?float $totalDebtToEquityMrq,
		#[Column(type: 'float', nullable: true)]
		public ?float $currentRatioMrq,
		#[Column(type: 'float', nullable: true)]
		public ?float $bookValuePerShareMrq,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $operatingCashFlowTtm,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $leveredFreeCashFlowTtm,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $sharesOutstanding,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $floatShares,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $avg10Volume,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $avg90Volume,
		#[Column(type: 'bigInteger', nullable: true)]
		public ?int $sharesShort,
		#[Column(type: 'float', nullable: true)]
		public ?float $shortRatio,
		#[Column(type: 'float', nullable: true)]
		public ?float $shortPercentOfSharesOutstanding,
		#[Column(type: 'float', nullable: true)]
		public ?float $percentHeldByInsiders,
		#[Column(type: 'float', nullable: true)]
		public ?float $percentHeldByInstitutions,
		#[Column(type: 'float', nullable: true)]
		public ?float $fiftyTwoWeekLow,
		#[Column(type: 'float', nullable: true)]
		public ?float $fiftyTwoWeekHigh,
		#[Column(type: 'float', nullable: true)]
		public ?float $fiftyTwoWeekChange,
		#[Column(type: 'float', nullable: true)]
		public ?float $beta,
		#[Column(type: 'float', nullable: true)]
		public ?float $day50Ma,
		#[Column(type: 'float', nullable: true)]
		public ?float $day200Ma,
		#[Column(type: 'float', nullable: true)]
		public ?float $forwardAnnualDividendRate,
		#[Column(type: 'float', nullable: true)]
		public ?float $forwardAnnualDividendYield,
		#[Column(type: 'float', nullable: true)]
		public ?float $trailingAnnualDividendRate,
		#[Column(type: 'float', nullable: true)]
		public ?float $trailingAnnualDividendYield,
		#[Column(type: 'float', nullable: true)]
		public ?float $fiveYearAverageDividendYield,
		#[Column(type: 'float', nullable: true)]
		public ?float $payoutRatio,
		#[Column(type: 'date', nullable: true)]
		public ?DateTimeImmutable $dividendDate,
		#[Column(type: 'date', nullable: true)]
		public ?DateTimeImmutable $exDividendDate,
	) {
	}
}
