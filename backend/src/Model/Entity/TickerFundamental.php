<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;
use FinGather\Model\Repository\TickerFundamentalRepository;

#[Entity(repository: TickerFundamentalRepository::class)]
class TickerFundamental extends AEntity
{
	public function __construct(
		#[RefersTo(target: Ticker::class)]
		private Ticker $ticker,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $marketCapitalization,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $enterpriseValue,
		#[Column(type: 'float', nullable: true)]
		private ?float $trailingPe,
		#[Column(type: 'float', nullable: true)]
		private ?float $forwardPe,
		#[Column(type: 'float', nullable: true)]
		private ?float $pegRatio,
		#[Column(type: 'float', nullable: true)]
		private ?float $priceToSalesTtm,
		#[Column(type: 'float', nullable: true)]
		private ?float $priceToBookMrq,
		#[Column(type: 'float', nullable: true)]
		private ?float $enterpriseToRevenue,
		#[Column(type: 'float', nullable: true)]
		private ?float $enterpriseToEbitda,
		#[Column(type: 'date', nullable: true)]
		private ?DateTimeImmutable $fiscalYearEnds,
		#[Column(type: 'date', nullable: true)]
		private ?DateTimeImmutable $mostRecentQuarter,
		#[Column(type: 'float', nullable: true)]
		private ?float $profitMargin,
		#[Column(type: 'float', nullable: true)]
		private ?float $operatingMargin,
		#[Column(type: 'float', nullable: true)]
		private ?float $returnOnAssetsTtm,
		#[Column(type: 'float', nullable: true)]
		private ?float $returnOnEquityTtm,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $revenueTtm,
		#[Column(type: 'float', nullable: true)]
		private ?float $revenuePerShareTtm,
		#[Column(type: 'float', nullable: true)]
		private ?float $quarterlyRevenueGrowth,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $grossProfitTtm,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $ebitda,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $netIncomeToCommonTtm,
		#[Column(type: 'float', nullable: true)]
		private ?float $dilutedEpsTtm,
		#[Column(type: 'float', nullable: true)]
		private ?float $quarterlyEarningsGrowthYoy,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $totalCashMrq,
		#[Column(type: 'float', nullable: true)]
		private ?float $totalCashPerShareMrq,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $totalDebtMrq,
		#[Column(type: 'float', nullable: true)]
		private ?float $totalDebtToEquityMrq,
		#[Column(type: 'float', nullable: true)]
		private ?float $currentRatioMrq,
		#[Column(type: 'float', nullable: true)]
		private ?float $bookValuePerShareMrq,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $operatingCashFlowTtm,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $leveredFreeCashFlowTtm,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $sharesOutstanding,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $floatShares,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $avg10Volume,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $avg90Volume,
		#[Column(type: 'bigInteger', nullable: true)]
		private ?int $sharesShort,
		#[Column(type: 'float', nullable: true)]
		private ?float $shortRatio,
		#[Column(type: 'float', nullable: true)]
		private ?float $shortPercentOfSharesOutstanding,
		#[Column(type: 'float', nullable: true)]
		private ?float $percentHeldByInsiders,
		#[Column(type: 'float', nullable: true)]
		private ?float $percentHeldByInstitutions,
		#[Column(type: 'float', nullable: true)]
		private ?float $fiftyTwoWeekLow,
		#[Column(type: 'float', nullable: true)]
		private ?float $fiftyTwoWeekHigh,
		#[Column(type: 'float', nullable: true)]
		private ?float $fiftyTwoWeekChange,
		#[Column(type: 'float', nullable: true)]
		private ?float $beta,
		#[Column(type: 'float', nullable: true)]
		private ?float $day50Ma,
		#[Column(type: 'float', nullable: true)]
		private ?float $day200Ma,
		#[Column(type: 'float', nullable: true)]
		private ?float $forwardAnnualDividendRate,
		#[Column(type: 'float', nullable: true)]
		private ?float $forwardAnnualDividendYield,
		#[Column(type: 'float', nullable: true)]
		private ?float $trailingAnnualDividendRate,
		#[Column(type: 'float', nullable: true)]
		private ?float $trailingAnnualDividendYield,
		#[Column(type: 'float', nullable: true)]
		private ?float $fiveYearAverageDividendYield,
		#[Column(type: 'float', nullable: true)]
		private ?float $payoutRatio,
		#[Column(type: 'date', nullable: true)]
		private ?DateTimeImmutable $dividendDate,
		#[Column(type: 'date', nullable: true)]
		private ?DateTimeImmutable $exDividendDate,
	) {
	}

	public function getTicker(): Ticker
	{
		return $this->ticker;
	}

	public function getMarketCapitalization(): ?int
	{
		return $this->marketCapitalization;
	}

	public function setMarketCapitalization(?int $marketCapitalization): void
	{
		$this->marketCapitalization = $marketCapitalization;
	}

	public function getEnterpriseValue(): ?int
	{
		return $this->enterpriseValue;
	}

	public function setEnterpriseValue(?int $enterpriseValue): void
	{
		$this->enterpriseValue = $enterpriseValue;
	}

	public function getTrailingPe(): ?float
	{
		return $this->trailingPe;
	}

	public function setTrailingPe(?float $trailingPe): void
	{
		$this->trailingPe = $trailingPe;
	}

	public function getForwardPe(): ?float
	{
		return $this->forwardPe;
	}

	public function setForwardPe(?float $forwardPe): void
	{
		$this->forwardPe = $forwardPe;
	}

	public function getPegRatio(): ?float
	{
		return $this->pegRatio;
	}

	public function setPegRatio(?float $pegRatio): void
	{
		$this->pegRatio = $pegRatio;
	}

	public function getPriceToSalesTtm(): ?float
	{
		return $this->priceToSalesTtm;
	}

	public function setPriceToSalesTtm(?float $priceToSalesTtm): void
	{
		$this->priceToSalesTtm = $priceToSalesTtm;
	}

	public function getPriceToBookMrq(): ?float
	{
		return $this->priceToBookMrq;
	}

	public function setPriceToBookMrq(?float $priceToBookMrq): void
	{
		$this->priceToBookMrq = $priceToBookMrq;
	}

	public function getEnterpriseToRevenue(): ?float
	{
		return $this->enterpriseToRevenue;
	}

	public function setEnterpriseToRevenue(?float $enterpriseToRevenue): void
	{
		$this->enterpriseToRevenue = $enterpriseToRevenue;
	}

	public function getEnterpriseToEbitda(): ?float
	{
		return $this->enterpriseToEbitda;
	}

	public function setEnterpriseToEbitda(?float $enterpriseToEbitda): void
	{
		$this->enterpriseToEbitda = $enterpriseToEbitda;
	}

	public function getFiscalYearEnds(): ?DateTimeImmutable
	{
		return $this->fiscalYearEnds;
	}

	public function setFiscalYearEnds(?DateTimeImmutable $fiscalYearEnds): void
	{
		$this->fiscalYearEnds = $fiscalYearEnds;
	}

	public function getMostRecentQuarter(): ?DateTimeImmutable
	{
		return $this->mostRecentQuarter;
	}

	public function setMostRecentQuarter(?DateTimeImmutable $mostRecentQuarter): void
	{
		$this->mostRecentQuarter = $mostRecentQuarter;
	}

	public function getProfitMargin(): ?float
	{
		return $this->profitMargin;
	}

	public function setProfitMargin(?float $profitMargin): void
	{
		$this->profitMargin = $profitMargin;
	}

	public function getOperatingMargin(): ?float
	{
		return $this->operatingMargin;
	}

	public function setOperatingMargin(?float $operatingMargin): void
	{
		$this->operatingMargin = $operatingMargin;
	}

	public function getReturnOnAssetsTtm(): ?float
	{
		return $this->returnOnAssetsTtm;
	}

	public function setReturnOnAssetsTtm(?float $returnOnAssetsTtm): void
	{
		$this->returnOnAssetsTtm = $returnOnAssetsTtm;
	}

	public function getReturnOnEquityTtm(): ?float
	{
		return $this->returnOnEquityTtm;
	}

	public function setReturnOnEquityTtm(?float $returnOnEquityTtm): void
	{
		$this->returnOnEquityTtm = $returnOnEquityTtm;
	}

	public function getRevenueTtm(): ?int
	{
		return $this->revenueTtm;
	}

	public function setRevenueTtm(?int $revenueTtm): void
	{
		$this->revenueTtm = $revenueTtm;
	}

	public function getRevenuePerShareTtm(): ?float
	{
		return $this->revenuePerShareTtm;
	}

	public function setRevenuePerShareTtm(?float $revenuePerShareTtm): void
	{
		$this->revenuePerShareTtm = $revenuePerShareTtm;
	}

	public function getQuarterlyRevenueGrowth(): ?float
	{
		return $this->quarterlyRevenueGrowth;
	}

	public function setQuarterlyRevenueGrowth(?float $quarterlyRevenueGrowth): void
	{
		$this->quarterlyRevenueGrowth = $quarterlyRevenueGrowth;
	}

	public function getGrossProfitTtm(): ?int
	{
		return $this->grossProfitTtm;
	}

	public function setGrossProfitTtm(?int $grossProfitTtm): void
	{
		$this->grossProfitTtm = $grossProfitTtm;
	}

	public function getEbitda(): ?int
	{
		return $this->ebitda;
	}

	public function setEbitda(?int $ebitda): void
	{
		$this->ebitda = $ebitda;
	}

	public function getNetIncomeToCommonTtm(): ?int
	{
		return $this->netIncomeToCommonTtm;
	}

	public function setNetIncomeToCommonTtm(?int $netIncomeToCommonTtm): void
	{
		$this->netIncomeToCommonTtm = $netIncomeToCommonTtm;
	}

	public function getDilutedEpsTtm(): ?float
	{
		return $this->dilutedEpsTtm;
	}

	public function setDilutedEpsTtm(?float $dilutedEpsTtm): void
	{
		$this->dilutedEpsTtm = $dilutedEpsTtm;
	}

	public function getQuarterlyEarningsGrowthYoy(): ?float
	{
		return $this->quarterlyEarningsGrowthYoy;
	}

	public function setQuarterlyEarningsGrowthYoy(?float $quarterlyEarningsGrowthYoy): void
	{
		$this->quarterlyEarningsGrowthYoy = $quarterlyEarningsGrowthYoy;
	}

	public function getTotalCashMrq(): ?int
	{
		return $this->totalCashMrq;
	}

	public function setTotalCashMrq(?int $totalCashMrq): void
	{
		$this->totalCashMrq = $totalCashMrq;
	}

	public function getTotalCashPerShareMrq(): ?float
	{
		return $this->totalCashPerShareMrq;
	}

	public function setTotalCashPerShareMrq(?float $totalCashPerShareMrq): void
	{
		$this->totalCashPerShareMrq = $totalCashPerShareMrq;
	}

	public function getTotalDebtMrq(): ?int
	{
		return $this->totalDebtMrq;
	}

	public function setTotalDebtMrq(?int $totalDebtMrq): void
	{
		$this->totalDebtMrq = $totalDebtMrq;
	}

	public function getTotalDebtToEquityMrq(): ?float
	{
		return $this->totalDebtToEquityMrq;
	}

	public function setTotalDebtToEquityMrq(?float $totalDebtToEquityMrq): void
	{
		$this->totalDebtToEquityMrq = $totalDebtToEquityMrq;
	}

	public function getCurrentRatioMrq(): ?float
	{
		return $this->currentRatioMrq;
	}

	public function setCurrentRatioMrq(?float $currentRatioMrq): void
	{
		$this->currentRatioMrq = $currentRatioMrq;
	}

	public function getBookValuePerShareMrq(): ?float
	{
		return $this->bookValuePerShareMrq;
	}

	public function setBookValuePerShareMrq(?float $bookValuePerShareMrq): void
	{
		$this->bookValuePerShareMrq = $bookValuePerShareMrq;
	}

	public function getOperatingCashFlowTtm(): ?int
	{
		return $this->operatingCashFlowTtm;
	}

	public function setOperatingCashFlowTtm(?int $operatingCashFlowTtm): void
	{
		$this->operatingCashFlowTtm = $operatingCashFlowTtm;
	}

	public function getLeveredFreeCashFlowTtm(): ?int
	{
		return $this->leveredFreeCashFlowTtm;
	}

	public function setLeveredFreeCashFlowTtm(?int $leveredFreeCashFlowTtm): void
	{
		$this->leveredFreeCashFlowTtm = $leveredFreeCashFlowTtm;
	}

	public function getSharesOutstanding(): ?int
	{
		return $this->sharesOutstanding;
	}

	public function setSharesOutstanding(?int $sharesOutstanding): void
	{
		$this->sharesOutstanding = $sharesOutstanding;
	}

	public function getFloatShares(): ?int
	{
		return $this->floatShares;
	}

	public function setFloatShares(?int $floatShares): void
	{
		$this->floatShares = $floatShares;
	}

	public function getAvg10Volume(): ?int
	{
		return $this->avg10Volume;
	}

	public function setAvg10Volume(?int $avg10Volume): void
	{
		$this->avg10Volume = $avg10Volume;
	}

	public function getAvg90Volume(): ?int
	{
		return $this->avg90Volume;
	}

	public function setAvg90Volume(?int $avg90Volume): void
	{
		$this->avg90Volume = $avg90Volume;
	}

	public function getSharesShort(): ?int
	{
		return $this->sharesShort;
	}

	public function setSharesShort(?int $sharesShort): void
	{
		$this->sharesShort = $sharesShort;
	}

	public function getShortRatio(): ?float
	{
		return $this->shortRatio;
	}

	public function setShortRatio(?float $shortRatio): void
	{
		$this->shortRatio = $shortRatio;
	}

	public function getShortPercentOfSharesOutstanding(): ?float
	{
		return $this->shortPercentOfSharesOutstanding;
	}

	public function setShortPercentOfSharesOutstanding(?float $shortPercentOfSharesOutstanding): void
	{
		$this->shortPercentOfSharesOutstanding = $shortPercentOfSharesOutstanding;
	}

	public function getPercentHeldByInsiders(): ?float
	{
		return $this->percentHeldByInsiders;
	}

	public function setPercentHeldByInsiders(?float $percentHeldByInsiders): void
	{
		$this->percentHeldByInsiders = $percentHeldByInsiders;
	}

	public function getPercentHeldByInstitutions(): ?float
	{
		return $this->percentHeldByInstitutions;
	}

	public function setPercentHeldByInstitutions(?float $percentHeldByInstitutions): void
	{
		$this->percentHeldByInstitutions = $percentHeldByInstitutions;
	}

	public function getFiftyTwoWeekLow(): ?float
	{
		return $this->fiftyTwoWeekLow;
	}

	public function setFiftyTwoWeekLow(?float $fiftyTwoWeekLow): void
	{
		$this->fiftyTwoWeekLow = $fiftyTwoWeekLow;
	}

	public function getFiftyTwoWeekHigh(): ?float
	{
		return $this->fiftyTwoWeekHigh;
	}

	public function setFiftyTwoWeekHigh(?float $fiftyTwoWeekHigh): void
	{
		$this->fiftyTwoWeekHigh = $fiftyTwoWeekHigh;
	}

	public function getFiftyTwoWeekChange(): ?float
	{
		return $this->fiftyTwoWeekChange;
	}

	public function setFiftyTwoWeekChange(?float $fiftyTwoWeekChange): void
	{
		$this->fiftyTwoWeekChange = $fiftyTwoWeekChange;
	}

	public function getBeta(): ?float
	{
		return $this->beta;
	}

	public function setBeta(?float $beta): void
	{
		$this->beta = $beta;
	}

	public function getDay50Ma(): ?float
	{
		return $this->day50Ma;
	}

	public function setDay50Ma(?float $day50Ma): void
	{
		$this->day50Ma = $day50Ma;
	}

	public function getDay200Ma(): ?float
	{
		return $this->day200Ma;
	}

	public function setDay200Ma(?float $day200Ma): void
	{
		$this->day200Ma = $day200Ma;
	}

	public function getForwardAnnualDividendRate(): ?float
	{
		return $this->forwardAnnualDividendRate;
	}

	public function setForwardAnnualDividendRate(?float $forwardAnnualDividendRate): void
	{
		$this->forwardAnnualDividendRate = $forwardAnnualDividendRate;
	}

	public function getForwardAnnualDividendYield(): ?float
	{
		return $this->forwardAnnualDividendYield;
	}

	public function setForwardAnnualDividendYield(?float $forwardAnnualDividendYield): void
	{
		$this->forwardAnnualDividendYield = $forwardAnnualDividendYield;
	}

	public function getTrailingAnnualDividendRate(): ?float
	{
		return $this->trailingAnnualDividendRate;
	}

	public function setTrailingAnnualDividendRate(?float $trailingAnnualDividendRate): void
	{
		$this->trailingAnnualDividendRate = $trailingAnnualDividendRate;
	}

	public function getTrailingAnnualDividendYield(): ?float
	{
		return $this->trailingAnnualDividendYield;
	}

	public function setTrailingAnnualDividendYield(?float $trailingAnnualDividendYield): void
	{
		$this->trailingAnnualDividendYield = $trailingAnnualDividendYield;
	}

	public function getFiveYearAverageDividendYield(): ?float
	{
		return $this->fiveYearAverageDividendYield;
	}

	public function setFiveYearAverageDividendYield(?float $fiveYearAverageDividendYield): void
	{
		$this->fiveYearAverageDividendYield = $fiveYearAverageDividendYield;
	}

	public function getPayoutRatio(): ?float
	{
		return $this->payoutRatio;
	}

	public function setPayoutRatio(?float $payoutRatio): void
	{
		$this->payoutRatio = $payoutRatio;
	}

	public function getDividendDate(): ?DateTimeImmutable
	{
		return $this->dividendDate;
	}

	public function setDividendDate(?DateTimeImmutable $dividendDate): void
	{
		$this->dividendDate = $dividendDate;
	}

	public function getExDividendDate(): ?DateTimeImmutable
	{
		return $this->exDividendDate;
	}

	public function setExDividendDate(?DateTimeImmutable $exDividendDate): void
	{
		$this->exDividendDate = $exDividendDate;
	}
}
