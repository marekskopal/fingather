<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dcf\Dto;

enum DcfValuationStatusEnum: string
{
	case Overvalued = 'overvalued';
	case Undervalued = 'undervalued';
	case FairlyValued = 'fairlyValued';
}
