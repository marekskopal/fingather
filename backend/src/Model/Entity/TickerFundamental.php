<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Repository\TickerFundamentalRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: TickerFundamentalRepository::class)]
class TickerFundamental extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: Ticker::class)]
		public readonly Ticker $ticker,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $marketCapitalization,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $enterpriseValue,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $trailingPe,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $forwardPe,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $pegRatio,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $priceToSalesTtm,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $priceToBookMrq,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $enterpriseToRevenue,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $enterpriseToEbitda,
		#[Column(type: Type::Date, nullable: true)]
		public ?DateTimeImmutable $fiscalYearEnds,
		#[Column(type: Type::Date, nullable: true)]
		public ?DateTimeImmutable $mostRecentQuarter,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $profitMargin,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $operatingMargin,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $returnOnAssetsTtm,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $returnOnEquityTtm,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $revenueTtm,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $revenuePerShareTtm,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $quarterlyRevenueGrowth,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $grossProfitTtm,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $ebitda,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $netIncomeToCommonTtm,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $dilutedEpsTtm,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $quarterlyEarningsGrowthYoy,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $totalCashMrq,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $totalCashPerShareMrq,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $totalDebtMrq,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $totalDebtToEquityMrq,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $currentRatioMrq,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $bookValuePerShareMrq,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $operatingCashFlowTtm,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $leveredFreeCashFlowTtm,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $sharesOutstanding,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $floatShares,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $avg10Volume,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $avg90Volume,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $sharesShort,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $shortRatio,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $shortPercentOfSharesOutstanding,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $percentHeldByInsiders,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $percentHeldByInstitutions,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $fiftyTwoWeekLow,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $fiftyTwoWeekHigh,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $fiftyTwoWeekChange,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $beta,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $day50Ma,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $day200Ma,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $forwardAnnualDividendRate,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $forwardAnnualDividendYield,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $trailingAnnualDividendRate,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $trailingAnnualDividendYield,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $fiveYearAverageDividendYield,
		#[Column(type: Type::Float, nullable: true)]
		public ?float $payoutRatio,
		#[Column(type: Type::Date, nullable: true)]
		public ?DateTimeImmutable $dividendDate,
		#[Column(type: Type::Date, nullable: true)]
		public ?DateTimeImmutable $exDividendDate,
	) {
	}
}
