<?php

declare(strict_types=1);

namespace FinGather\Dto\Enum;

enum AssetOrderEnum: string
{
	case TickerName = 'tickerName';
	case Price = 'price';
	case Units = 'units';
	case Value = 'value';
	case Gain = 'gain';
	case DividendYield = 'dividendYield';
	case FxImpact = 'fxImpact';
	case Return = 'return';
}
