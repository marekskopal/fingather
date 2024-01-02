<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum TransactionCreateTypeEnum: string
{
	case Manual = 'Manual';
	case Import = 'Import';
}
