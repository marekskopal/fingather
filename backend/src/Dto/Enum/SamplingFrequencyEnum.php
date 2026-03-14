<?php

declare(strict_types=1);

namespace FinGather\Dto\Enum;

enum SamplingFrequencyEnum: string
{
	case Daily = 'Daily';
	case Weekly = 'Weekly';
	case Monthly = 'Monthly';
}
