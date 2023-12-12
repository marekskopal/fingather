<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use FinGather\Model\Repository\CurrencyRepository;

#[Entity(repository: CurrencyRepository::class)]
class Currency extends AEntity
{
	public function __construct(
		#[Column(type: 'string(3)')]
		private string $code,
		#[Column(type: 'string(50)')]
		private string $name,
		#[Column(type: 'string(5)')]
		private string $symbol,
	) {
	}

	public function getCode(): string
	{
		return $this->code;
	}

	public function setCode(string $code): void
	{
		$this->code = $code;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getSymbol(): string
	{
		return $this->symbol;
	}

	public function setSymbol(string $symbol): void
	{
		$this->symbol = $symbol;
	}
}
