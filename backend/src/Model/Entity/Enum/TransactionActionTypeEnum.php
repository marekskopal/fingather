<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum TransactionActionTypeEnum: string
{
	case Undefined = 'Undefined';
	case Buy = 'Buy';
	case Sell = 'Sell';
	case Dividend = 'Dividend';
	case Tax = 'Tax';
	case Fee = 'Fee';
	case DividendTax = 'DividendTax';

	public static function fromString(string $string): self
	{
		if (
			str_contains($string, 'buy')
			|| str_contains($string, 'nákup')
			|| str_contains($string, 'otevřená')
		) {
			return TransactionActionTypeEnum::Buy;
		}

		if (
			str_contains($string, 'sell')
			|| str_contains($string, 'prodej')
			|| str_contains($string, 'zavřená')
		) {
			return TransactionActionTypeEnum::Sell;
		}

		if (str_contains($string, 'dividend')) {
			if (
				str_contains($string, 'tax')
				|| str_contains($string, 'daň')
			) {
				return TransactionActionTypeEnum::DividendTax;
			}

			return TransactionActionTypeEnum::Dividend;
		}

		if (
			str_contains($string, 'tax')
			|| str_contains($string, 'daň')
		) {
			return TransactionActionTypeEnum::Tax;
		}

		if (
			str_contains($string, 'fee')
			|| str_contains($string, 'poplatek')
		) {
			return TransactionActionTypeEnum::Fee;
		}

		return TransactionActionTypeEnum::Undefined;
	}
}
