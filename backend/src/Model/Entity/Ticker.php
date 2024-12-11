<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Repository\TickerRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: TickerRepository::class)]
class Ticker extends AEntity
{
	public function __construct(
		#[Column(type: 'string(20)')]
		private string $ticker,
		#[Column(type: 'string')]
		private string $name,
		#[ManyToOne(entityClass: Market::class)]
		private Market $market,
		#[ManyToOne(entityClass: Currency::class)]
		private Currency $currency,
		#[ColumnEnum(enum: TickerTypeEnum::class, default: TickerTypeEnum::Stock->value)]
		private TickerTypeEnum $type,
		#[Column(type: 'string', nullable: true)]
		private ?string $isin,
		#[Column(type: 'string', nullable: true)]
		private ?string $logo,
		#[ManyToOne(entityClass: Sector::class)]
		private Sector $sector,
		#[ManyToOne(entityClass: Industry::class)]
		private Industry $industry,
		#[Column(type: 'string', nullable: true)]
		private ?string $website,
		#[Column(type: 'text', nullable: true)]
		private ?string $description,
		#[ManyToOne(entityClass: Country::class)]
		private Country $country,
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

	public function getSector(): Sector
	{
		return $this->sector;
	}

	public function setSector(Sector $sector): void
	{
		$this->sector = $sector;
	}

	public function getIndustry(): Industry
	{
		return $this->industry;
	}

	public function setIndustry(Industry $industry): void
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

	public function getCountry(): Country
	{
		return $this->country;
	}

	public function setCountry(Country $country): void
	{
		$this->country = $country;
	}
}
