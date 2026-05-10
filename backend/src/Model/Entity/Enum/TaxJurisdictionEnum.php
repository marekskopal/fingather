<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum TaxJurisdictionEnum: string
{
	case CzechRepublic = 'CzechRepublic';
	case Slovakia = 'Slovakia';
	case Germany = 'Germany';
	case Generic = 'Generic';
}
