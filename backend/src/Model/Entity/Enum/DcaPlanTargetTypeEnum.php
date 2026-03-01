<?php

declare(strict_types=1);

namespace FinGather\Model\Entity\Enum;

enum DcaPlanTargetTypeEnum: string
{
	case Portfolio = 'Portfolio';
	case Asset = 'Asset';
	case Group = 'Group';
	case Strategy = 'Strategy';
}
