<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum BrokerImportTypeEnum: string
{
	case Trading212 = 'Trading212';
	case Revolut = 'Revolut';
	case Anycoin = 'Anycoin';
}
