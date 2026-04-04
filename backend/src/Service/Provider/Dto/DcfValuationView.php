<?php

declare(strict_types=1);

namespace FinGather\Service\Provider\Dto;

use FinGather\Model\Entity\Ticker;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfInputs;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfResult;

final readonly class DcfValuationView
{
	public function __construct(
		public Ticker $ticker,
		public DcfInputs $inputs,
		public DcfResult $result,
	) {
	}
}
