<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum TransactionActionTypeEnum: string
{
	case Undefined = 'Undefined';
	case Buy = 'Buy';
	case Sell = 'Sell';
	case Dividend = 'Dividend';

	public static function fromString(string $string): self
	{
		if (strpos($string, 'buy') !== false) {
			return TransactionActionTypeEnum::Buy;
		}

		if (strpos($string, 'sell') !== false) {
			return TransactionActionTypeEnum::Sell;
		}

		if (strpos($string, 'dividend') !== false) {
			return TransactionActionTypeEnum::Dividend;
		}

		return TransactionActionTypeEnum::Undefined;
	}
}
