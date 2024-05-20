<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Repository\TickerRepository;

#[Entity(repository: TickerRepository::class)]
class Ticker extends AEntity
{
	public function __construct(
		#[Column(type: 'string(20)')]
		private string $ticker,
		#[Column(type: 'string')]
		private string $name,
		#[RefersTo(target: Market::class)]
		private Market $market,
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
		#[Column(type: 'enum(Stock,Etf,Crypto)', default: TickerTypeEnum::Stock->value, typecast: TickerTypeEnum::class)]
		private TickerTypeEnum $type,
		#[Column(type: 'string', nullable: true)]
		private ?string $isin,
		#[Column(type: 'string', nullable: true)]
		private ?string $logo,
		#[RefersTo(target: TickerSector::class, nullable: true)]
		private ?TickerSector $sector,
		#[RefersTo(target: TickerIndustry::class, nullable: true)]
		private ?TickerIndustry $industry,
		#[Column(type: 'string', nullable: true)]
		private ?string $website,
		#[Column(type: 'text', nullable: true)]
		private ?string $description,
		#[RefersTo(target: Country::class, nullable: true)]
		private ?Country $country,
	) {
	}

	public function getTicker(): string
	{
		return $this->ticker;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getMarket(): Market
	{
		return $this->market;
	}

	public function getIsin(): ?string
	{
		return $this->isin;
	}

	public function setIsin(?string $isin): void
	{
		$this->isin = $isin;
	}

	public function getCurrency(): Currency
	{
		return $this->currency;
	}

	public function getType(): TickerTypeEnum
	{
		return $this->type;
	}

	public function setType(TickerTypeEnum $type): void
	{
		$this->type = $type;
	}

	public function getLogo(): ?string
	{
		return $this->logo;
	}

	public function setLogo(?string $logo): void
	{
		$this->logo = $logo;
	}

	public function getSector(): ?TickerSector
	{
		return $this->sector;
	}

	public function setSector(?TickerSector $sector): void
	{
		$this->sector = $sector;
	}

	public function getIndustry(): ?TickerIndustry
	{
		return $this->industry;
	}

	public function setIndustry(?TickerIndustry $industry): void
	{
		$this->industry = $industry;
	}

	public function getWebsite(): ?string
	{
		return $this->website;
	}

	public function setWebsite(?string $website): void
	{
		$this->website = $website;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(?string $description): void
	{
		$this->description = $description;
	}

	public function getCountry(): ?Country
	{
		return $this->country;
	}

	public function setCountry(?Country $country): void
	{
		$this->country = $country;
	}
}
