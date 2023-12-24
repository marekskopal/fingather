<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum MarketTypeEnum: string
{
	case Stock = 'Stock';
	case Crypto = 'Crypto';
}
