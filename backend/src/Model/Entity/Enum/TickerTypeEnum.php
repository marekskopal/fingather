<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum TickerTypeEnum: string
{
	case Stock = 'Stock';
	case Etf = 'Etf';
	case Crypto = 'Crypto';
}
