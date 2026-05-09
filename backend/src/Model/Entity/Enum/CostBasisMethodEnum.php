<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum CostBasisMethodEnum: string
{
	case Fifo = 'Fifo';
	case Lifo = 'Lifo';
	case AverageCost = 'AverageCost';
}
