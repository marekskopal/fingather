<?php

declare(strict_types=1);

namespace FinGather\Model\Repository\Enum;

enum OrderDirectionEnum: string
{
	case ASC = 'asc';
	case DESC = 'desc';
}
