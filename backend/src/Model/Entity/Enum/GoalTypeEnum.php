<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum GoalTypeEnum: string
{
	case PortfolioValue = 'PortfolioValue';
	case ReturnPercentage = 'ReturnPercentage';
	case InvestedAmount = 'InvestedAmount';
}
