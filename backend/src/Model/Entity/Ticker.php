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
		#[Column(type: 'string', nullable: true)]
		private ?string $sector,
		#[Column(type: 'string', nullable: true)]
		private ?string $industry,
		#[Column(type: 'string', nullable: true)]
		private ?string $website,
		#[Column(type: 'text', nullable: true)]
		private ?string $description,
		#[Column(type: 'string', nullable: true)]
		private ?string $country,
	) {
	}

	public function getTicker(): string
	{
		return $this->ticker;
	}

	public function setTicker(string $ticker): void
	{
		$this->ticker = $ticker;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getMarket(): Market
	{
		return $this->market;
	}

	public function setMarket(Market $market): void
	{
		$this->market = $market;
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

	public function setCurrency(Currency $currency): void
	{
		$this->currency = $currency;
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

	public function getSector(): ?string
	{
		return $this->sector;
	}

	public function setSector(?string $sector): void
	{
		$this->sector = $sector;
	}

	public function getIndustry(): ?string
	{
		return $this->industry;
	}

	public function setIndustry(?string $industry): void
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

	public function getCountry(): ?string
	{
		return $this->country;
	}

	public function setCountry(?string $country): void
	{
		$this->country = $country;
	}
}
