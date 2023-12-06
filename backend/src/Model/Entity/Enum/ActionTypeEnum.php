<?php

namespace FinGather\Model\Entity\Enum;

enum ActionTypeEnum: string
{
	case Undefined = 'Undefined';
	case Buy = 'Buy';
	case Sell = 'Sell';
}
