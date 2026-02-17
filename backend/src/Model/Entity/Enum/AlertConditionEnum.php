<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum AlertConditionEnum: string
{
	case Above = 'Above';
	case Below = 'Below';
}
