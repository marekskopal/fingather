<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dcf;

use FinGather\Service\DataCalculator\Dcf\Dto\DcfAssumptions;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfInputs;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfResult;

interface DcfCalculatorInterface
{
	public function calculate(DcfInputs $inputs, DcfAssumptions $assumptions): DcfResult;
}
