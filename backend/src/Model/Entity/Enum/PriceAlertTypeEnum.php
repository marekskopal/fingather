<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum PriceAlertTypeEnum: string
{
	case Price = 'Price';
	case Portfolio = 'Portfolio';
}
