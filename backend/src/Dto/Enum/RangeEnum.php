<?php

declare(strict_types=1);

namespace FinGather\Dto\Enum;

enum RangeEnum: string
{
	case SevenDays = 'SevenDays';
	case OneMonth = 'OneMonth';
	case ThreeMonths = 'ThreeMonths';
	case SixMonths = 'SixMonths';
	case YTD = 'YTD';
	case OneYear = 'OneYear';
	case All = 'All';
	case Custom = 'Custom';
}
