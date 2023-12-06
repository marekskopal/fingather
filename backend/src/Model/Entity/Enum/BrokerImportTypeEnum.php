<?php

namespace FinGather\Model\Entity\Enum;

enum BrokerImportTypeEnum: string
{
	case Trading212 = 'Trading212';
	case Revolut = 'Revolut';
}
