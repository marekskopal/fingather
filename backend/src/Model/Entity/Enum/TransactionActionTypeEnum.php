<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum TransactionActionTypeEnum: string
{
	case Undefined = 'Undefined';
	case Buy = 'Buy';
	case Sell = 'Sell';
}
